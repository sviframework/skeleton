<?php

namespace Svi\BaseBundle;

class ContainerAware extends \Svi\ContainerAware
{
	/**
	 * @var \Svi\BaseBundle\Container
	 */
	protected $c;

	function __construct(\Svi\Application $app)
	{
		$this->c = Container::getInstance($app);
	}

}