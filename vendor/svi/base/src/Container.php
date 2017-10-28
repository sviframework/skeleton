<?php

namespace Svi\BaseBundle;

class Container extends \Svi\Container
{

	/**
	 * @return Bundle
	 */
	public function getSviBaseBundle()
	{
		return $this->getApp()[Bundle::class];
	}

	/**
	 * @return \Svi\MailBundle\Bundle
	 */
	public function getSviMailBundle()
	{
		return $this->getApp()[\Svi\MailBundle\Bundle::class];
	}

	/**
	 * @return \Svi\FileBundle\Bundle
	 */
	public function getSviFileBundle()
	{
		return $this->getApp()[\Svi\FileBundle\Bundle::class];
	}

	/**
	 * @return \Svi\CrudBundle\Bundle
	 */
	public function getSviCrudBundle()
	{
		return $this->getApp()[\Svi\CrudBundle\Bundle::class];
	}

} 