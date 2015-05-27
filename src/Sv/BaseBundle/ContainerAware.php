<?php

namespace Sv\BaseBundle;

class ContainerAware extends \Svi\ContainerAware
{
	/**
	 * @var \Sv\BaseBundle\Container
	 */
	protected $c;

	function __construct(\Svi\Application $app)
	{
		$this->c = Container::getInstance($app);
	}

}