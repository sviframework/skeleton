<?php

namespace Svi\Base;

class Container extends \Svi\Container
{

	/**
	 * @return Bundle
	 */
	public function getSviBaseBundle()
	{
		return $this->getApp()->get('bundle.svibase');
	}

	/**
	 * @return \Svi\Mail\Bundle
	 */
	public function getSviMailBundle()
	{
		return $this->getApp()->get('bundle.svimail');
	}

	/**
	 * @return \Svi\File\Bundle
	 */
	public function getSviFileBundle()
	{
		return $this->getApp()->get('bundle.svifile');
	}

	/**
	 * @return \Svi\File\Bundle
	 */
	public function getSviCrudBundle()
	{
		return $this->getApp()->get('bundle.svifile');
	}

} 