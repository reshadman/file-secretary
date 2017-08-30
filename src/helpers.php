<?php

if ( ! function_exists('fs_asset')) {
    function fs_asset($assetFolder, $afterPath, $force = false)
    {
        return \Reshadman\FileSecretary\Infrastructure\UrlGenerator::asset($assetFolder, $afterPath, $force);
    }
}