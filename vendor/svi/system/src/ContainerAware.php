<?php

namespace Svi;

abstract class ContainerAware
{
	/**
	 * @var Container
	 */
	protected $c;

	function __construct(Application $app)
	{

	}

	/**
	 * @return \Doctrine\DBAL\Query\QueryBuilder
	 */
	public function createQB()
	{
		return $this->c->getDb()->createQueryBuilder();
	}

	public function getParameter($key)
	{
		return $this->c->getApp()->getConfigService()->getParameter($key);
	}

	public function getDb($schema = 'default')
	{
		return $this->c->getDb($schema);
	}

	public function getRequest()
	{
		return $this->c->getRequest();
	}

} 