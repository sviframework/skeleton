<?php

namespace Svi\File\Twig;

use Svi\Application;

class ImageTwigExtension extends \Twig_Extension
{
	/**
	 * @var Application
	 */
	private $app;

	function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'twig.svi_image';
	}

	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter('imageResize', [$this, 'imageResizeFunction']),
		];
	}

	public function imageResizeFunction($image, $width, $height, $mode = 0)
	{
		return $this->app->get('service.sviimage')->getImagePath($image, $width, $height, $mode);
	}

}