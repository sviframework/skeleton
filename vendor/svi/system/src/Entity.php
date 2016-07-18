<?php

namespace Svi;

use \Doctrine\DBAL\Connection;
use \Doctrine\DBAL\Schema\Column;
use \Doctrine\DBAL\Schema\Schema;
use \Doctrine\DBAL\Schema\Table;
use \Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;

/**
 * reserved: $fields, getFields, getFieldValue
 */
abstract class Entity
{
	static private $cache = array();
	/** @var Connection $connection */
	static public $connection;
	private $loadedFormDb = false;
	private $loadedData = [];

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
	 * @return Table
	 * @throws \Exception
	 */
	final public function getTableSchema()
	{
		if (!isset(self::$cache['schema'])) {
			self::$cache['schema'] = new Schema();
		}
		if (!isset(self::$cache[get_class($this)]['table'])) {
			/** @var \Doctrine\DBAL\Schema\Table $table */
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
				/** @var Entity $entity */
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
	 * @return Column[]
	 * @throws \Exception
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
	 * @param bool $onlyChanged
	 * @return array
	 */
	final public function getDataArray($onlyChanged = false, $updateLoadedData = false)
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
			if ($onlyChanged && array_key_exists($schema->getName(), $this->loadedData)) {
				if ($schema->getType() == 'Boolean') {
					if ($value === ($this->loadedData[$schema->getName()] ? true : false)) {
						continue;
					}
				}
				if ($value === $this->loadedData[$schema->getName()]) {
					continue;
				}
			}
			if ($updateLoadedData) {
				$this->loadedData[$schema->getName()] = $value;
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
	 * @param $fieldName
	 * @return mixed
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
	 * @param $fieldName
	 * @param $value
	 * @return mixed
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
	 * @throws \Exception
	 */
	final public function setFieldValueByDbKey($dbFieldName, $value)
	{
		$this->getTableSchema();
		if (!@self::$cache[get_class($this)]['db_to_field'][$dbFieldName]) {
			throw new \Exception('There is no field mapped to ' . $dbFieldName . ' in ' . get_class($this));
		}
		$fieldName = self::$cache[get_class($this)]['db_to_field'][$dbFieldName];
		$method = 'set' . ucfirst($fieldName);

		/** @var Column $columnSchema */
		$columnSchema = self::$cache[get_class($this)]['columns'][$fieldName];
		if ($columnSchema->getType() == 'Array') {
			if (!is_array($value)) {
				$value = $value ? unserialize($value) : [];
			}
		} elseif ($columnSchema->getType() == 'Boolean') {
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
		$this->loadedData = $data;
		foreach ($data as $key => $value) {
			if ($key == $this->getIdColumnName()) {
				$this->loadedFormDb = true;
			}
			$this->setFieldValueByDbKey($key, $value);
		}

		return $this;
	}

	public function save(Connection $connection = null)
	{
		if (!$connection) {
			$connection = self::$connection;
		}
		if ($this->loadedFormDb) {
			$data = $this->getDataArray(true, true);
			if (count($data)) {
				$connection->update($this->getTableName(), $data, [$this->getIdColumnName() => $this->getFieldValue($this->getIdFieldName())]);
			}
		} else {
			$data = $this->getDataArray(false, true);
			if (count($data)) {
				$connection->insert($this->getTableName(), $data);
			}
			$this->setFieldValue($this->getIdFieldName(), $connection->lastInsertId());
		}
		self::$cache[get_class($this)]['fetch'] = [];
		$this->loadedFormDb = true;

		return $this;
	}

	public function delete(Connection $connection = null)
	{
		if (!$connection) {
			$connection = self::$connection;
		}
		if ($this->loadedFormDb) {
			$connection->delete($this->getTableName(), array($this->getIdColumnName() => $this->getFieldValue($this->getIdFieldName())));
			self::$cache[get_class($this)]['fetch'] = [];
		}
	}

	/**
	 * @param QueryBuilder $qb
	 * @param null $noCache
	 * @return $this|null
	 */
	public static function fetchOne(QueryBuilder $qb, $noCache = null)
	{
		$result = self::fetch($qb, $noCache);

		return array_key_exists(0, $result) ? $result[0] : null;
	}

	public static function fetch(QueryBuilder $qb, $noCache = null)
	{
		$entity = new static();
		$className = get_class($entity);
		$qb->resetQueryPart('from');
		$columnNames = [];
		foreach ($entity->getDbColumnNames() as $n) {
			$columnNames[] = 'e.' . $n;
		}
		$qb->select(implode(', ', $columnNames))->from($entity->getTableName(), 'e');

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
		$result = [];
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

	public static function findOneBy(array $criteria = [], array $orderBy = null, $noCache = null, Connection $connection = null)
	{
		$result = self::findBy($criteria, $orderBy, 1, null, $noCache, $connection);

		return @$result[0];
	}

	public static function findBy(array $criteria = [], array $orderBy = null, $limit = null, $offset = null, $noCache = null, Connection $connection = null)
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
				/** @var Column $column */
				$column = $columns[$col];
				if ($val === null) {
					$db->andWhere($column->getName() . " IS NULL");
				} else {
					$db->andWhere($column->getName() . " = :$col")->setParameter($col, $val);
				}
			}
		}
		if (is_array($orderBy) && count($orderBy)) {
			foreach ($orderBy as $col => $val) {
				if (!isset($columns[$col])) {
					throw new \Exception('There is no field "' . $col . '" in ' . $className);
				}
				/** @var Column $column */
				$column = $columns[$col];
				$db->addOrderBy($column->getName(), $val);
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