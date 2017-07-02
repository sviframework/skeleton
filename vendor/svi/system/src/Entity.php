<?php

namespace Svi;

abstract class Entity
{
	private $loadedData = [];
	private $loadedFromDb = false;

	/**
	 * @return array
	 */
	public function getLoadedData()
	{
		return $this->loadedData;
	}

	/**
	 * @param array $loadedData
	 * @return $this
	 */
	public function setLoadedData(array $loadedData)
	{
		$this->loadedData = $loadedData;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getLoadedFromDb()
	{
		return $this->loadedFromDb;
	}

	/**
	 * @param bool $loadedFromDb
	 * @return $this
	 */
	public function setLoadedFromDb($loadedFromDb)
	{
		$this->loadedFromDb = $loadedFromDb;

		return $this;
	}

}