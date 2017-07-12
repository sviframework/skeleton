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
		if ($app->getTwig()) {
			$app->getTwig()->addExtension(new ImageTwigExtension($app));
		}
	}

	protected function getServices()
	{
		return [
			'service.svifile' => 'Service\FileService',
			'service.sviimage' => 'Service\ImageService',
		];
	}

	/**
	 * @return FileService
	 */
	public function getFileService()
	{
		return $this->getApp()->get('service.svifile');
	}

	/**
	 * @return ImageService
	 */
	public function getImageService()
	{
		return $this->getApp()->get('service.sviimage');
	}

} 