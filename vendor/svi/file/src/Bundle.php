<?php

namespace Svi\File;

use Svi\Application;
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
			'manager.svifile' => 'FileManager',
			'manager.sviimage' => 'ImageManager',
		];
	}

} 