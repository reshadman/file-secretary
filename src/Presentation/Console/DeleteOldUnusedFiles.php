<?php

namespace Reshadman\FileSecretary\Presentation\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Reshadman\FileSecretary\Application\Usecases\DeleteTrackedFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class DeleteOldUnusedFiles extends Command
{
    protected $signature = 'file-secretary:delete-old-unused-files {--minutes=}';

    protected $description = "Deletes all files with no usage at given minutes before now.";

    /**
     * @param FileSecretaryManager $fileSecretaryManager
     */
    public function handle(FileSecretaryManager $fileSecretaryManager, DeleteTrackedFile $deleteTrackedFile)
    {
        $minutes = $this->option('minutes');

        $minutes = trim($minutes);

        if (empty($minutes)) {
            $minutes = 60 * 24; // 1 day.
        }

        $minutes = (int)$minutes;

        $this->comment("Deleting all files before {$minutes} from now, without no usage.");

        $time = Carbon::now()->subMinutes($minutes)->toDateTimeString();

        $this->comment("Deleting files without usage before {$time}");

        $model = $fileSecretaryManager->getPersistModel();

        $query = $model->newInstance()->where('updated_at', '<', $time)->where('used_times', '<=', 0);

        while (($result = $query->first()) !== null) {
            $deleteTrackedFile->execute($result, DeleteTrackedFile::ON_DELETE_DELETE_IF_NOT_IN_OTHERS);
            $this->comment("Deleted file {$result->id}.");
        }

        $this->comment('Finished deleting.');
    }
}