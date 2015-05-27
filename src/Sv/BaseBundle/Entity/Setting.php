<?php

namespace Sv\BaseBundle\Entity;

class Setting extends \Svi\Entity
{
	private $id;
	private $key;
	private $value;

	/**
	 * Must return fields in like that: DB_field_name => classFieldName
	 */
	public function getFields()
	{
		return [
			'id' => ['id', 'integer', 'id'],
			'key' => ['skey', 'string', 'length' => 64, 'unique'],
			'value' => ['value', 'text', 'null'],
		];
	}

	/**
	 * Must return table name in SQL DB where entity stored
	 */
	public function getTableName()
	{
		return 'setting';
	}

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