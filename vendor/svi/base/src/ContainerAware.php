<?php

namespace Svi\Base;

class ContainerAware extends \Svi\ContainerAware
{
	/**
	 * @var \Svi\Base\Container
	 */
	protected $c;

	function __construct(\Svi\Application $app)
	{
		$this->c = Container::getInstance($app);
	}

}