<?php

namespace Svi\FileBundle;

use Svi\FileBundle\Service\FileService;
use Svi\FileBundle\Service\ImageService;

trait BundleTrait
{
    use \Svi\Service\BundlesService\BundleTrait;

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