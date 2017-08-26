<?php

namespace Reshadman\FileSecretary\Application;

use Illuminate\Support\Str;
use Reshadman\FileSecretary\Application\Events\AfterAssetUpload;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class AfterAssetUploadEventHandler
{
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;

    public function __construct(FileSecretaryManager $secretaryManager)
    {
        $this->secretaryManager = $secretaryManager;
    }

    public function handle(AfterAssetUpload $event)
    {
        $assetTagData = $this->secretaryManager->getConfig('asset_folders.' . $event->assetTag);

        $envKey = $assetTagData['env_key'];

        $currentEnv = env($envKey, null);
        $current = null;
        $basePath = app()->environmentPath() . '/' . app()->environmentFile();

        if ($currentEnv !== null) {
            $handle = fopen($basePath, 'r');

            while (($line = fgets($handle)) !== false) {
                if (Str::startsWith($line, $envKey)) {
                    $current = $line;
                }
            }

            fclose($handle);

            $contents = file_get_contents($basePath);

            $envStr = $envKey . '=' . $event->newUniqueKey;

            $contents = str_replace($current, $envKey . '=' . $event->newUniqueKey . "\n", $contents);

            file_put_contents($basePath, $contents);
        } else {

            $contents = file_get_contents($basePath);

            $contents .= "\n" . ($envStr = $envKey.'='.$event->newUniqueKey);

            file_put_contents($basePath, $contents);
        }

        putenv($envStr);

        $tagStart = $this->secretaryManager->getAssetStartingPath($assetTagData['context'], $event->assetTag);

        $driver = $this->secretaryManager->getContextDriver($assetTagData['context']);

        foreach ($driver->directories($tagStart) as $directory) {
            if (!Str::endsWith($directory, [$event->newUniqueKey, $currentEnv])) {
                $driver->deleteDirectory($directory);
            }
        }
    }
}