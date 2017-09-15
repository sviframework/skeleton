<?php

namespace Svi\Base;

class Container extends \Svi\Container
{

	/**
	 * @return Bundle
	 */
	public function getSviBaseBundle()
	{
		return $this->getApp()->get(Bundle::class);
	}

	/**
	 * @return \Svi\Mail\Bundle
	 */
	public function getSviMailBundle()
	{
		return $this->getApp()->get(\Svi\Mail\Bundle::class);
	}

	/**
	 * @return \Svi\File\Bundle
	 */
	public function getSviFileBundle()
	{
		return $this->getApp()->get(\Svi\File\Bundle::class);
	}

	/**
	 * @return \Svi\Crud\Bundle
	 */
	public function getSviCrudBundle()
	{
		return $this->getApp()->get(\Svi\Crud\Bundle::class);
	}

} 