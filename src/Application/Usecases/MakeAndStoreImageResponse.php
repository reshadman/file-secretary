<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Infrastructure\Images\MadeImageResponse;

class MakeAndStoreImageResponse
{
    /**
     * @var MadeImageResponse
     */
    private $madeImageResponse;
    /**
     * @var AddressableRemoteFile
     */
    private $remoteFile;

    public function __construct(MadeImageResponse $madeImageResponse, AddressableRemoteFile $remoteFile)
    {
        $this->madeImageResponse = $madeImageResponse;
        $this->remoteFile = $remoteFile;
    }

    /**
     * @return AddressableRemoteFile
     */
    public function getRemoteFile()
    {
        return $this->remoteFile;
    }

    /**
     * @return MadeImageResponse
     */
    public function getMadeImageResponse()
    {
        return $this->madeImageResponse;
    }
}