<?php

namespace Svi\File;

use Svi\File\Service\FileService;
use Svi\File\Service\ImageService;

trait BundleTrait
{
    use \Svi\BundleTrait;

    /**
     * @return FileService
     */
    public function getFileService()
    {
        return $this->get(FileService::class);
    }

    /**
     * @return ImageService
     */
    public function getImageService()
    {
        return $this->get(ImageService::class);
    }
}