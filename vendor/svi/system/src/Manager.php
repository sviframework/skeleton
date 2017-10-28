<?php

namespace Svi;

use \Doctrine\DBAL\Schema\Schema;
use \Doctrine\DBAL\Schema\Table;
use \Doctrine\DBAL\Schema\Column;
use \Doctrine\DBAL\Connection;
use \Doctrine\DBAL\Query\QueryBuilder;
use Svi\CrudBundle\Entity\NestedSortableInterface;
use Svi\CrudBundle\Entity\RemovableInterface;
use Svi\CrudBundle\Entity\SortableInterface;

// Singleton

abstract class Manager implements ManagerInterface
{
	/** @var Application */
	protected $app;
	private $schemaName = null;
	/** @var Schema[] Schema */
	private static $schemas = [];
	private $cache = [];
	/** @var \ReflectionClass */
	private $reflection = null;

	public function __construct(Application $app, $schemaName = 'default')
	{
		$this->app = $app;
		$this->schemaName = $schemaName;
	}

	public function getSchemaName()
	{
		return $this->schemaName;
	}

	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->app['dbs'][$this->schemaName];
	}

	/**
	 * Must return fields in like that: classFieldName => Column schema
	 */
	abstract public function getDbFieldsDefinition();

	/**
	 * Must return table name in SQL DB where entity stored
	 */
	abstract public function getTableName();

	abstract public function getEntityClassName();

	/**
	 * @return \Doctrine\DBAL\Schema\Schema
	 */
	public function getDbSchema()
	{
		return self::$schemas[$this->getSchemaName()];
	}

	/**
	 * @return Table
	 * @throws \Exception
	 */
	public function getTableSchema()
	{
		if (!array_key_exists($this->getSchemaName(), self::$schemas)) {
			self::$schemas[$this->getSchemaName()] = new Schema();
		}
		if (!self::$schemas[$this->getSchemaName()]->hasTable($this->getTableName())) {
			/** @var \Doctrine\DBAL\Schema\Table $table */
			$table = self::$schemas[$this->getSchemaName()]->createTable($this->getTableName());
			$this->cache['table'] = $table;

			$dbColumnsToFieldNames = [];
			$fieldToColumnNames = [];
			$columns = [];
			foreach ($this->getDbFieldsDefinition() as $key => $value) {
				$column = $table->addColumn($value[0], $value[1]);
				if (count($value) > 2) {
					$i = 0;
					foreach ($value as $pKey => $pVal) {
						$i++;
						if ($i < 3)	continue;
						if ($pVal === 'id') {
							$column->setNotnull(true);
							$column->setAutoincrement(true);
							$table->setPrimaryKey(array($value[0]));
							$this->cache['idFieldName'] = $key;
							$this->cache['idColumnName'] = $column->getName();
						} elseif ($pVal === 'ai') {
							$column->setAutoincrement(true);
						} elseif ($pVal === 'null') {
							$column->setNotnull(false);
						} elseif($pVal === 'unique') {
							$table->addUniqueIndex(array($column->getName()));
						} elseif($pVal === 'index') {
							$table->addIndex(array($column->getName()));
						} elseif($pVal === 'unsigned') {
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
                                case 'default': $column->setDefault($pVal);break;
								default: throw new \Exception("Unsupported parameter \"$pVal\" for column \"$key\"");
							}
						}
					}
				}
				if (!$table->getPrimaryKey()) {
					throw new \Exception('There is no primary key for ' . $this->getEntityClassName());
				}
				$columns[$key] = $column;
				$dbColumnsToFieldNames[$column->getName()] = $key;
				$fieldToColumnNames[$key] = $column->getName();
			}
			foreach ($this->getIndexes() as $cols) {
				$table->addIndex($cols);
			}
			foreach ($this->getForeigners() as $manager => $params) {
				/** @var Manager $foreignManager */
				$foreignManager = call_user_func($manager . '::getInstance("' . $this->schemaName . '")');
				$table->addForeignKeyConstraint($foreignManager->getTableSchema(),
					is_array($params[0]) ? $params[0] : array($params[0]),
					is_array($params[1]) ? $params[1] : array($params[1]), isset($params[2]) ? $params[2] : array());
			}

			$this->cache['columns'] = $columns;
			$this->cache['db_to_field'] = $dbColumnsToFieldNames;
			$this->cache['field_to_db'] = $fieldToColumnNames;
			$this->cache['table'] = $table;
		}

		return $this->cache['table'];
	}

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
	public function getIndexes()
	{
		return [];
	}

	/**
	 * Must return table foreign keys constraints in that format:
	 * return [
	 *   'My\TestBundle\Manager\SomeForeignManager' => [['ourTableDbColumn1', 'ourTableDbColumn1', ...], ['foreignTableDbColumn1', 'foreignTableDbColumn2', ...]],
	 *   'My\TestBundle\Manager\OtherForeignManager' =>[['ourTableDbColumn2', 'ourTableDbColumn4', ...], ['foreignTableDbColumn2', 'foreignTableDbColumn4', ...]],
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
	public function getForeigners()
	{
		return [];
	}

	/**
	 * Return schemas in like that: classFieldName => Column schema
	 * @return Column[]
	 * @throws \Exception
	 */
	public function getColumnsSchemas()
	{
		if (!array_key_exists('columns', $this->cache)) {
			$this->getTableSchema();
		}

		return $this->cache['columns'];
	}

	/**
	 * Returns field value by class private field name
	 * @param Entity $entity
	 * @param $fieldName
	 * @return mixed
	 */
	public function getFieldValue(Entity $entity, $fieldName)
	{
		$method = 'get' . ucfirst($fieldName);

		return $entity->$method();
	}

	/**
	 * @param Entity $entity
	 * @param $fieldName
	 * @param $value
	 * @return mixed
	 */
	public function setFieldValue(Entity $entity, $fieldName, $value)
	{
		$method = 'set' . ucfirst($fieldName);

		return $entity->$method($value);
	}

	/**
	 * @param $dbFieldName
	 * @return mixed
	 * @throws \Exception
	 */
	public function getFieldValueByDbKey(Entity $entity, $dbFieldName)
	{
		$this->getTableSchema();
		if (!array_key_exists('db_to_field', $this->cache) && !array_key_exists($dbFieldName, $this->cache['db_to_field'])) {
			throw new \Exception('There is no field mapped to ' . $dbFieldName . ' in ' . $this->getEntityClassName());
		}
		$method = 'get' . ucfirst($this->cache['db_to_field'][$dbFieldName]);

		return $entity->$method();
	}

	/**
	 * Sets class field value by DB field name
	 *
	 * @param $dbFieldName
	 * @param $value
	 * @throws \Exception
	 */
	public function setFieldValueByDbKey(Entity $entity, $dbFieldName, $value)
	{
		$this->getTableSchema();
		if (!array_key_exists('db_to_field', $this->cache) && !array_key_exists($dbFieldName, $this->cache['db_to_field'])) {
			throw new \Exception('There is no field mapped to ' . $dbFieldName . ' in ' . get_class($this));
		}
		$fieldName = $this->cache['db_to_field'][$dbFieldName];
		$method = 'set' . ucfirst($fieldName);

		/** @var Column $columnSchema */
		$columnSchema = $this->cache['columns'][$fieldName];
		if ($columnSchema->getType() == 'Array') {
			if (!is_array($value)) {
				$value = $value ? json_decode($value, true) : [];
			}
		} elseif ($columnSchema->getType() == 'Boolean') {
			$value = $value ? true : false;
		}

		$entity->$method($value);
	}

	/**
	 * Returns class field name which is primary ID field
	 *
	 * @return string
	 */
	public function getIdFieldName()
	{
		$this->getTableSchema();

		return $this->cache['idFieldName'];
	}

	/**
	 * Returns DB field name which is primary ID field
	 *
	 * @return mixed
	 */
	public function getIdColumnName()
	{
		$this->getTableSchema();

		return $this->cache['idColumnName'];
	}

	public function getDbColumnNames()
	{
		$this->getTableSchema();

		return $this->cache['field_to_db'];
	}


	/**
	 * Returns array which used for update or insert SQL operations
	 *
	 * @param Entity $entity
	 * @param bool $onlyChanged
	 * @param bool $updateLoadedData
	 * @return array
	 */
	public function getDataArray(Entity $entity, $onlyChanged = false, $updateLoadedData = false)
	{
		$result = array();

		foreach ($this->getColumnsSchemas() as $fieldName => $schema) {
			$value = $this->getFieldValue($entity, $fieldName);
			if ($schema->getType() == 'Array') {
				if (!is_array($value) || !$value) {
					$value = json_encode([]);
				} else {
					$value = json_encode($value);
				}
			}
			if ($onlyChanged && array_key_exists($schema->getName(), $entity->getLoadedData())) {
				if ($schema->getType() == 'Boolean') {
					if ($value === ($entity->getLoadedData()[$schema->getName()] ? true : false)) {
						continue;
					}
				}
				if ($value === $entity->getLoadedData()[$schema->getName()]) {
					continue;
				}
			}
			if ($updateLoadedData) {
				$entity->getLoadedData()[$schema->getName()] = $value;
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
     * @param array $data
     * @return Entity[]
     */
	public function getListByData(array $data)
    {
        $result = [];

        foreach ($data as $entityData) {
            $result[] = $this->fillByData($entityData);
        }

        return $result;
    }

	/**
	 * Fills class fields by SQL returned data
	 *
	 * @param array $data
	 * @param Entity|null $entity
	 * @return Entity
	 */
	public function fillByData(array $data, Entity $entity = null)
	{
		$entity = $entity ? $entity : $this->createEntity();
		$entity->setLoadedData($data);
		foreach ($data as $key => $value) {
			if ($key == $this->getIdColumnName()) {
				$entity->setLoadedFromDb(true);
			}
			$this->setFieldValueByDbKey($entity, $key, $value);
		}

		return $entity;
	}

	public function save(Entity $entity)
	{
		$connection = $this->getConnection();
		if ($entity->getLoadedFromDb()) {
			$data = $this->getDataArray($entity,true, true);
			if (count($data)) {
				$connection->update($this->getTableName(), $data, [$this->getIdColumnName() => $this->getFieldValue($entity, $this->getIdFieldName())]);
			}
		} else {
			$data = $this->getDataArray($entity, false, true);
			if (count($data)) {
			    if ($this->isPostgresql()) {
			        $columns = $this->getColumnsSchemas();
			        if ($this->getIdColumnName() && $columns[$this->getIdColumnName()]->getAutoincrement()) {
			            unset($data[$this->getIdColumnName()]);
                    }
                }
				$connection->insert($this->getTableName(), $data);
			}
			$this->setFieldValue($entity, $this->getIdFieldName(), $connection->lastInsertId());
		}
		$this->cache['fetch'] = [];
		$entity->setLoadedFromDb(true);

		return $entity;
	}

	public function delete(Entity $entity)
	{
		$connection = $this->getConnection();
		if ($entity->getLoadedFromDb()) {
			$connection->delete($this->getTableName(), array($this->getIdColumnName() => $this->getFieldValue($entity, $this->getIdFieldName())));
			$this->cache['fetch'] = [];
		}
	}

	/**
	 * @param QueryBuilder $qb
	 * @param null $noCache
	 * @return Entity|null
	 */
	public function fetchOne(QueryBuilder $qb, $noCache = null)
	{
		$result = $this->fetch($qb, $noCache);

		return array_key_exists(0, $result) ? $result[0] : null;
	}

	/**
	 * @param QueryBuilder $qb
	 * @param null $noCache
	 * @return Entity[]
	 */
	public function fetch(QueryBuilder $qb, $noCache = null)
	{
		$qb->resetQueryPart('from');
		$columnNames = [];
		foreach ($this->getDbColumnNames() as $n) {
			$columnNames[] = 'e.' . $n;
		}
		$tableName = $this->isPostgresql() ?  '"' . $this->getTableName() . '"' : $this->getTableName();
		$qb->select(implode(', ', $columnNames))->from($tableName, 'e');

		$cacheKey = null;
		if (!$noCache) {
			$cacheKey = $qb->getSQL();
			foreach ($qb->getParameters() as $k => $v) {
				$cacheKey .= $k . $v;
			}
		}

		if ($cacheKey) {
			if (array_key_exists('fetch', $this->cache) && array_key_exists($cacheKey, $this->cache['fetch'])) {
				return $this->cache['fetch'][$cacheKey];
			}
		}
		$result = [];
		$items = $qb->execute()->fetchAll();
		if (count($items)) {
			foreach ($items as $e) {
				$result[] = $this->fillByData($e);
			}
		}
		if ($cacheKey) {
			if (!array_key_exists('fetch', $this->cache)) {
				$this->cache['fetch'] = [];
			}
			$this->cache['fetch'][$cacheKey] = $result;
		}

		return $result;
	}

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param null $limit
	 * @param null $offset
	 * @param null $noCache
	 * @return Entity[]
	 * @throws \Exception
	 */
	public function findBy(array $criteria = [], array $orderBy = null, $limit = null, $offset = null, $noCache = null)
	{
		$connection = $this->getConnection();

		$db = $connection->createQueryBuilder();
        if (count($criteria)) {
            foreach ($criteria as $col => $val) {
                if ($val === null) {
                    $db->andWhere($col . " IS NULL");
                } elseif (is_array($val)) {
                    $db->andWhere($col . " IN (" . implode(', ', $val) . ')');
                } elseif (is_numeric($col)) {
                    $db->andWhere($val);
                } else {
                    $db->andWhere($col . " = :$col")->setParameter($col, $val);
                }
            }
        }
		if (is_array($orderBy) && count($orderBy)) {
			foreach ($orderBy as $col => $val) {
				$db->addOrderBy($col, $val);
			}
		}
		if ($limit !== null) {
			$db->setMaxResults($limit);
		}
		if ($offset != null) {
			$db->setFirstResult($offset);
		}

		return $this->fetch($db, $noCache);
	}

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param null $noCache
	 * @return Entity|null
	 */
	public function findOneBy(array $criteria = [], array $orderBy = null, $noCache = null)
	{
		$result = self::findBy($criteria, $orderBy, 1, null, $noCache);

		return count($result) ? $result[0] : null;
	}

	public function __call($name, $arguments) {
	    $getTableColName = function ($field) {
            $field = lcfirst($field);
            $columns = $this->getDbColumnNames();
            if (!array_key_exists($field, $columns)) {
                throw new \Exception('There is no field ' . $field . ' in entity ' . $this->getEntityClassName());
            }

            return $columns[$field];
        };

		if (preg_match('/^findBy(.*)$/', $name, $matches)) {
			if (count($arguments) < 1) {
				throw new \Exception('Too few parameters');
			}

			return self::findBy([$getTableColName($matches[1]) => $arguments[0]],
                isset($arguments[1]) ? $arguments[1] : null,
                isset($arguments[2]) ? $arguments[2] : null,
                isset($arguments[3]) ? $arguments[3] : null,
                isset($arguments[4]) ? $arguments[4] : null);
		} elseif (preg_match('/^findOneBy(.*)$/', $name, $matches)) {
			if (count($arguments) < 1) {
				throw new \Exception('Too few parameters');
			}

			return $this->findOneBy([$getTableColName($matches[1]) => $arguments[0]],
                isset($arguments[1]) ? $arguments[1] : null,
                isset($arguments[2]) ? $arguments[2] : null);
		}

		throw new \ErrorException ('Call to Undefined Method ' . get_called_class() . '::' . $name . '()', 0, E_ERROR);
	}

	/**
	 * @return \ReflectionClass
	 */
    protected function getReflection()
	{
		if ($this->reflection === null) {
			$this->reflection = new \ReflectionClass($this->getEntityClassName());
		}

		return $this->reflection;
	}

	public function isSortable()
	{
		return $this->getReflection()->implementsInterface(SortableInterface::class) ||
			$this->getReflection()->implementsInterface(NestedSortableInterface::class);
	}

	public function isNestedSortable()
	{
		return $this->getReflection()->implementsInterface(NestedSortableInterface::class);
	}

	public function isRemovable()
	{
		return $this->getReflection()->implementsInterface(RemovableInterface::class);
	}

	/**
	 * @return Entity
	 */
	public function createEntity()
	{
		$className = $this->getEntityClassName();

		return new $className();
	}

	public function isPostgresql()
    {
        return $this->getConnection()->getDatabasePlatform()->getName() == 'postgresql';
    }

}