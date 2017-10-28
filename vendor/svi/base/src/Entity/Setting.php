<?php

namespace Svi\BaseBundle\Entity;

use Svi\Entity;

class Setting extends Entity
{
	private $id;
	private $key;
	private $value;

	/**
	 * @param mixed $id
	 * @return Setting
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $key
	 * @return Setting
	 */
	public function setKey($key)
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param mixed $value
	 * @return Setting
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	function __toString()
	{
		return $this->getKey();
	}

} 