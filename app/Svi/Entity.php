<?php

namespace Svi;

/**
 * reserved: $fields, getFields, getFieldValue
 */
abstract class Entity
{
	static private $cache = array();
	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	static public $connection;

	function __construct(array $data = null)
	{
		if ($data) {
			$this->fillByData($data);
		}
		if (!isset(self::$cache[get_class($this)])) {
			self::$cache[get_class($this)] = array();
		}
	}

	/**
	 * @return \Doctrine\DBAL\Schema\Schema
	 */
	public static function getDbSchema()
	{
		return @self::$cache['schema'];
	}

	/**
	 * @return \Doctrine\DBAL\Schema\Table
	 */
	final public function getTableSchema()
	{
		if (!isset(self::$cache['schema'])) {
			self::$cache['schema'] = new \Doctrine\DBAL\Schema\Schema();
		}
		if (!isset(self::$cache[get_class($this)]['table'])) {
			$table = self::$cache['schema']->createTable($this->getTableName());
			self::$cache[get_class($this)]['table'] = $table;

			$dbColumnsToFieldNames = [];
			$fieldToColumnNames = [];
			$columns = array();
			foreach ($this->getFields() as $key => $value) {
				$column = $table->addColumn($value[0], $value[1]);
				if (count($value) > 2) {
					$i = 0;
					foreach ($value as $pKey => $pVal) {
						$i++;
						if ($i < 3)	continue;
						if ($pVal == 'id') {
							$column->setNotnull(true);
							$column->setAutoincrement(true);
							$table->setPrimaryKey(array($value[0]));
							self::$cache[get_class($this)]['idFieldName'] = $key;
							self::$cache[get_class($this)]['idColumnName'] = $column->getName();
						} elseif ($pVal == 'ai') {
							$column->setAutoincrement(true);
						} elseif ($pVal === 'null') {
							$column->setNotnull(false);
						} elseif($pVal == 'unique') {
							$table->addUniqueIndex(array($column->getName()));
						} elseif($pVal == 'index') {
							$table->addIndex(array($column->getName()));
						} elseif($pVal == 'unsigned') {
							$column->setUnsigned(true);
						} else {
							switch (strtolower($pKey)) {
								case 'length': $column->setLength($pVal);break;
								case 'precision': $column->setPrecision($pVal);break;
								case 'scale': $column->setScale($pVal);break;
								case 'unsigned': $column->setUnsigned($pVal);break;
								case 'fixed': $column->setFixed($pVal);break;
								case 'notnull': $column->setNotnull($pVal);break;
								case 'ai': $column->setAutoincrement($pVal);break;
								case 'comment': $column->setComment($pVal);break;
								default: throw new \Exception("Unsupported parameter \"$pVal\" for column \"$key\"");
							}
						}
					}
				}
				if (!$table->getPrimaryKey()) {
					throw new \Exception('There is no primary key for ' . get_class($this));
				}
				$columns[$key] = $column;
				$dbColumnsToFieldNames[$column->getName()] = $key;
				$fieldToColumnNames[$key] = $column->getName();
			}
			foreach ($this->getIndexes() as $cols) {
				$table->addIndex($cols);
			}
			foreach ($this->getForeigners() as $entity => $params) {
				$entity = new $entity();
				$table->addForeignKeyConstraint($entity->getTableSchema(),
					is_array($params[0]) ? $params[0] : array($params[0]),
					is_array($params[1]) ? $params[1] : array($params[1]), isset($params[2]) ? $params[2] : array());
				unset($entity);
			}

			self::$cache[get_class($this)]['columns'] = $columns;
			self::$cache[get_class($this)]['db_to_field'] = $dbColumnsToFieldNames;
			self::$cache[get_class($this)]['field_to_db'] = $fieldToColumnNames;
			self::$cache[get_class($this)]['table'] = $table;
		}

		return self::$cache[get_class($this)]['table'];
	}

	/**
	 * Return schemas in like that: classFieldName => Column schema
	 */
	final public function getColumnsSchemas()
	{
		if (!isset(self::$cache[get_class($this)]['columns'])) {
			$this->getTableSchema();
		}

		return self::$cache[get_class($this)]['columns'];
	}

	/**
	 * Must return fields in like that: classFieldName => Column schema
	 */
	abstract protected function getFields();

	/**
	 * Must return table name in SQL DB where entity stored
	 */
	abstract public function getTableName();

	/**
	 * Must return table indexes in that format:
	 * return [
	 *   ['tableDbColumn1', 'tableDbColumn2', ...],
	 *   ['tableDbColumn4', 'tableDbColumn7', ...],
	 *   ...
	 * ];
	 *
	 * If you want to add one-column index, just use 'index' field parameter in getFields()
	 *
	 * @return array
	 */
	public function getIndexes(){return [];}

	/**
	 * Must return table foreign keys constraints in that format:
	 * return [
	 *   'My\TestBundle\Entity\SomeForeignEntity' => [['ourTableDbColumn1', 'ourTableDbColumn1', ...], ['foreignTableDbColumn1', 'foreignTableDbColumn2', ...]],
	 *   'My\TestBundle\Entity\OtherForeignEntity' =>[['ourTableDbColumn2', 'ourTableDbColumn4', ...], ['foreignTableDbColumn2', 'foreignTableDbColumn4', ...]],
	 *   ...
	 * ];
	 *
	 * or simply:
	 *
	 * return [
	 *   'My\TestBundle\Entity\SomeForeignEntity' => ['ourTableDbColumn1', 'foreignTableDbColumn1', ['onDelete' => 'cascade']],
	 *   'My\TestBundle\Entity\OtherForeignEntity' =>['ourTableDbColumn2', 'foreignTableDbColumn2'],
	 *   ...
	 * ];
	 *
	 * @return array
	 */
	public function getForeigners(){return [];}

	/**
	 * Returns class field name which is primary ID field
	 *
	 * @return string
	 */
	final public function getIdFieldName()
	{
		$this->getTableSchema();

		return self::$cache[get_class($this)]['idFieldName'];
	}

	/**
	 * Returns DB field name which is primary ID field
	 *
	 * @return mixed
	 */
	final public function getIdColumnName()
	{
		$this->getTableSchema();

		return self::$cache[get_class($this)]['idColumnName'];
	}

	final public function getDbColumnNames()
	{
		$this->getTableSchema();

		return self::$cache[get_class($this)]['field_to_db'];
	}

	/**
	 * Returns array which used for update or insert SQL operations
	 *
	 * @return array
	 */
	final public function getDataArray()
	{
		$result = array();

		foreach ($this->getColumnsSchemas() as $fieldName => $schema) {
			$value = $this->getFieldValue($fieldName);
			if ($schema->getType() == 'Array') {
				if (!is_array($value) || !$value) {
					$value = serialize([]);
				} else {
					$value = serialize($value);
				}
			}
			if (!$value) {
				if ($value === false) {
					$value = '0';
				} elseif ($value === 0) {
					$value = '0';
				}
			}
			$result[$schema->getName()] = $value;
		}

		return $result;
	}

	/**
	 * Returns field value by class private field name
	 */
	final public function getFieldValue($fieldName)
	{
		$method = 'get' . ucfirst($fieldName);

		return $this->$method();
	}

	/**
	 * Gets class field value by DB field name
	 *
	 * @param $dbFieldName
	 * @param $value
	 * @return null
	 */
	final public function getFieldValueByDbKey($dbFieldName)
	{
		$this->getTableSchema();
		if (!@self::$cache[get_class($this)]['db_to_field'][$dbFieldName]) {
			return null;
		}
		$method = 'get' . ucfirst(self::$cache[get_class($this)]['db_to_field'][$dbFieldName]);

		return $this->$method();
	}

	/**
	 * Sets field value by class private field name
	 */
	final public function setFieldValue($fieldName, $value)
	{
		$method = 'set' . ucfirst($fieldName);

		return $this->$method($value);
	}

	/**
	 * Sets class field value by DB field name
	 *
	 * @param $dbFieldName
	 * @param $value
	 * @return null
	 */
	final public function setFieldValueByDbKey($dbFieldName, $value)
	{
		$this->getTableSchema();
		if (!@self::$cache[get_class($this)]['db_to_field'][$dbFieldName]) {
			throw new \Exception('There is no field mapped to ' . $dbFieldName . ' in ' . get_class($this));
		}
		$fieldName = self::$cache[get_class($this)]['db_to_field'][$dbFieldName];
		$method = 'set' . ucfirst($fieldName);

		if (self::$cache[get_class($this)]['columns'][$fieldName]->getType() == 'Array') {
			if (!is_array($value)) {
				$value = $value ? unserialize($value) : [];
			}
		} elseif (self::$cache[get_class($this)]['columns'][$fieldName]->getType() == 'Boolean') {
			$value = $value ? true : false;
		}

		$this->$method($value);
	}

	/**
	 * Fills class fields by SQL returned data
	 *
	 * @param array $data
	 * @return $this
	 */
	final public function fillByData(array $data)
	{
		foreach ($data as $key => $value) {
			if ($key == $this->getIdColumnName()) {
				$this->thisEntityWasLoadedFromDb = true;
			}
			$this->setFieldValueByDbKey($key, $value);
		}

		return $this;
	}

	public function save(\Doctrine\DBAL\Connection $connection = null)
	{
		if (!$connection) {
			$connection = self::$connection;
		}
		if (@$this->thisEntityWasLoadedFromDb) {
			$connection->update($this->getTableName(), $this->getDataArray(),
				array($this->getIdColumnName() => $this->getFieldValue($this->getIdFieldName())));
		} else {
			$connection->insert($this->getTableName(), $this->getDataArray());
			$this->setFieldValue($this->getIdFieldName(), $connection->lastInsertId());
		}
		self::$cache[get_class($this)]['fetch'] = [];
		$this->thisEntityWasLoadedFromDb = true;

		return $this;
	}

	public function delete(\Doctrine\DBAL\Connection $connection = null)
	{
		if (!$connection) {
			$connection = self::$connection;
		}
		if (@$this->thisEntityWasLoadedFromDb) {
			$connection->delete($this->getTableName(), array($this->getIdColumnName() => $this->getFieldValue($this->getIdFieldName())));
			self::$cache[get_class($this)]['fetch'] = [];
		}
	}

	/**
	 * @param \Doctrine\DBAL\Query\QueryBuilder $qb
	 * @return $this|null
	 */
	public static function fetchOne(\Doctrine\DBAL\Query\QueryBuilder $qb, $noCache = null)
	{
		$result = self::fetch($qb, $noCache);

		return @$result[0];
	}

	public static function fetch(\Doctrine\DBAL\Query\QueryBuilder $qb, $noCache = null)
	{
		$entity = new static();
		$className = get_class($entity);
		$qb->select(implode(', ', $entity->getDbColumnNames()))->from($entity->getTableName(), '');

		$cacheKey = null;
		if (!$noCache) {
			$cacheKey = $qb->getSQL();
			foreach ($qb->getParameters() as $k => $v) {
				$cacheKey .= $k . $v;
			}
		}

		if ($cacheKey) {
			if (isset(self::$cache[$className]['fetch'][$cacheKey])) {
				return self::$cache[$className]['fetch'][$cacheKey];
			}
		}
		$result = array();
		$items = $qb->execute()->fetchAll();
		if (count($items)) {
			foreach ($items as $e) {
				if (isset($entity)) {
					$entity->fillByData($e);
					$result[] = $entity;
					unset($entity);
				} else {
					$result[] = new static($e);
				}
			}
		} else {
			unset($entity);
		}
		if ($cacheKey) {
			if (!isset(self::$cache[$className]['fetch'])) {
				self::$cache[$className]['fetch'] = [];
			}
			self::$cache[$className]['fetch'][$cacheKey] = $result;
		}

		return $result;
	}

	public static function findOneBy(array $criteria = [], array $orderBy = null, $noCache = null, \Doctrine\DBAL\Connection $connection = null)
	{
		$result = self::findBy($criteria, $orderBy, 1, null, $noCache, $connection);

		return @$result[0];
	}

	public static function findBy(array $criteria = [], array $orderBy = null, $limit = null, $offset = null, $noCache = null, \Doctrine\DBAL\Connection $connection = null)
	{
		if (!$connection) {
			$connection = self::$connection;
		}

		$db = $connection->createQueryBuilder();
		$entity = new static();
		$columns = $entity->getColumnsSchemas();
		$className = get_class($entity);
		unset($entity);
		if (count($criteria)) {
			foreach ($criteria as $col => $val) {
				if (!isset($columns[$col])) {
					throw new \Exception('There is no field "' . $col . '" in ' . $className);
				}
				if ($val === null) {
					$db->andWhere($columns[$col]->getName() . " IS NULL");
				} else {
					$db->andWhere($columns[$col]->getName() . " = :$col")->setParameter($col, $val);
				}
			}
		}
		if (is_array($orderBy) && count($orderBy)) {
			foreach ($orderBy as $col => $val) {
				if (!isset($columns[$col])) {
					throw new \Exception('There is no field "' . $col . '" in ' . $className);
				}
				$db->addOrderBy($columns[$col]->getName(), $val);
			}
		}
		if ($limit !== null) {
			$db->setMaxResults($limit);
		}
		if ($offset != null) {
			$db->setFirstResult($offset);
		}

		return self::fetch($db, $noCache);
	}

	public static function __callStatic($name, $arguments) {
		if (preg_match('/^findBy(.*)$/', $name, $matches)) {
			if (count($arguments) < 1) {
				throw new \Exception('Too few parameters');
			}
			return self::findBy([lcfirst($matches[1]) => $arguments[0]], @$arguments[1], @$arguments[2], @$arguments[3], @$arguments[4], @$arguments[6]);
		} elseif (preg_match('/^findOneBy(.*)$/', $name, $matches)) {
			if (count($arguments) < 1) {
				throw new \Exception('Too few parameters');
			}

			return self::findOneBy([lcfirst($matches[1]) => $arguments[0]], @$arguments[1], @$arguments[2], @$arguments[3]);
		}

		throw new \ErrorException ('Call to Undefined Method ' . get_called_class() . '::' . $name . '()', 0, E_ERROR);
	}

}