<?php

namespace Svi\Base\Manager;

use Svi\Base\Entity\Setting;
use Svi\Manager;

class SettingManager extends Manager
{
	/**
	 * Must return fields in like that: DB_field_name => classFieldName
	 */
	public function getDbFieldsDefinition()
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

	public function getEntityClassName()
	{
		return Setting::class;
	}


}