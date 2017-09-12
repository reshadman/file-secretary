<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Carbon\Carbon;
use Reshadman\FileSecretary\Application\EloquentPersistedFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class UseOrUnuseTrackedFile
{
    /**
     * @var FileSecretaryManager
     */
    private $fileSecretaryManager;

    public function __construct(FileSecretaryManager $fileSecretaryManager)
    {
        $this->fileSecretaryManager = $fileSecretaryManager;
    }

    public function execute($fileUuidOrInstance, $times = 1)
    {
        $pdo = ($model = $this->fileSecretaryManager->getPersistModel())->getConnection()->getPdo();

        /** @var EloquentPersistedFile $file */
        if (is_string($fileUuidOrInstance)) {
            $file = $model->whereUuid($fileUuidOrInstance)->firstOrFail();
        } else {
            $file = $fileUuidOrInstance;
        }

        if ($pdo instanceof \PDO) {
            $statement = $pdo->prepare(
                "UPDATE " . $model->getTable() . " SET used_times = used_times + ?, updated_at = ? where id = ?"
            );

            $statement->execute([(int)$times, Carbon::now()->toDateTimeString(), $file->id]);
        } else {
            $file->used_times = $fileUuidOrInstance->used_times + $times;
            $file->save();
        }
    }
}