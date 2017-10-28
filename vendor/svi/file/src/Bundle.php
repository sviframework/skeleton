<?php

namespace Svi\FileBundle;

use Svi\Application;
use Svi\FileBundle\Service\FileService;
use Svi\FileBundle\Service\ImageService;
use Svi\FileBundle\Twig\ImageTwigExtension;

class Bundle extends \Svi\Service\BundlesService\Bundle
{
    use BundleTrait;

	function __construct(Application $app)
	{
		parent::__construct($app);
		if ($app->getTemplateService()->hasTwig()) {
			$app->getTemplateService()->getTwig()->addExtension(new ImageTwigExtension($app));
		}
	}

	protected function getServices()
	{
		return [
			FileService::class,
			ImageService::class,
		];
	}

} 