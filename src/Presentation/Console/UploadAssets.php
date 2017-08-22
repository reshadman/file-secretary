<?php

namespace Reshadman\FileSecretary\Presentation\Console;

use Illuminate\Console\Command;
use Reshadman\FileSecretary\Application\Usecases\UploadAsAssetCommand;

class UploadAssets extends Command
{
    protected $signature = 'file-secretary:upload-assets {--tags}';

    protected $description = "Takes a list of comma separated tags and mutates .env";

    /**
     * @param UploadAsAssetCommand $uploadCommand
     */
    public function handle(UploadAsAssetCommand $uploadCommand)
    {
        $tags = $this->option('tags');

        $tags = explode(',', $tags);

        if (empty($tags)) {
            throw new \InvalidArgumentException("Invalid tag given.");
        }

        foreach ($tags as $tag) {

            $newKey = $uploadCommand->execute($tag);

            $this->comment("Uploaded {$tag}:{$newKey}");
        }
    }
}