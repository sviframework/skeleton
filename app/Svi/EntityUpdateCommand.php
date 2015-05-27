<?php

namespace Svi;

class EntityUpdateCommand extends ConsoleCommand
{

	public function getName()
	{
		return 'db:update';
	}

	public function getDescription()
	{
		return 'List an SQL which need to update of database schema';
	}

	public function execute(array $args)
	{
		$execute = in_array('--execute', $args);

		if (!count($sqls = $this->getUpdateSchemaSql())) {
			$this->writeLn('There is no updates');
		} else {
			if (!$execute) {
				$this->writeLn('This SQL commans need to be executed to synchronize database.');
				$this->writeLn('You can execute it manual or run command: "php app/console db:update --execute"');
				$this->writeLn(strtoupper('P.S. Making database backup will be a good idea!'));
			} else {
				$this->writeLn('Executing commands:');
			}
			foreach ($this->getUpdateSchemaSql() as $sql) {
				$this->writeLn();
				$this->writeLn($sql);
				if ($execute) {
					$this->getApp()->getDb()->exec($sql);
				}
			}
		}
	}

	protected function getUpdateSchemaSql()
	{
		foreach ($this->getApp()->getBundles()->getEntityClasses() as $c) {
			$r = new \ReflectionClass($c);
			if (!$r->isInterface()) {
				$entity = new $c();
				$entity->getTableSchema();
			}
		}

		$schema = Entity::getDbSchema();
		if ($schema) {
			$dbSchema = $this->getApp()->getDb()->getSchemaManager()->createSchema();
			return $schema->getMigrateFromSql($dbSchema, $this->getApp()->getDb()->getDatabasePlatform());
		}

		return [];
	}

} 