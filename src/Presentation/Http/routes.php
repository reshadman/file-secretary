<?php

Route::get('file-secretary/{context_name}/{context_folder}/{after_context_path}', [
    'uses' => '\Reshadman\FileSecretary\Presentation\Http\Actions\DownloadFileAction@action',
    'as' => 'file-secretary.get.download_file',
    'middleware' => [\Reshadman\FileSecretary\Presentation\Http\Middleware\PrivacyCheckMiddleware::class]
]);
