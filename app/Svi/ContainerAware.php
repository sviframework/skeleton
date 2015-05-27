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
		return $this->c->getApp()->getConfig()->getParameter($key);
	}

	public function getDb()
	{
		return $this->c->getDb();
	}

	public function getRequest()
	{
		return $this->c->getRequest();
	}

} 