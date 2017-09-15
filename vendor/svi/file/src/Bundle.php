<?php

namespace Svi\File;

use Svi\Application;
use Svi\File\Service\FileService;
use Svi\File\Service\ImageService;
use Svi\File\Twig\ImageTwigExtension;

class Bundle extends \Svi\Bundle
{

	function __construct(Application $app)
	{
		parent::__construct($app);
		if ($app->getTemplateProcessor()->hasTwig()) {
			$app->getTemplateProcessor()->getTwig()->addExtension(new ImageTwigExtension($app));
		}
	}

	protected function getServices()
	{
		return [
			FileService::class,
			ImageService::class,
		];
	}

	/**
	 * @return FileService
	 */
	public function getFileService()
	{
		return $this->getApp()->get(FileService::class);
	}

	/**
	 * @return ImageService
	 */
	public function getImageService()
	{
		return $this->getApp()->get(ImageService::class);
	}

} 