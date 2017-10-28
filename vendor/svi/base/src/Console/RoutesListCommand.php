<?php

namespace Svi\BaseBundle\Console;

use Svi\Service\ConsoleService\ConsoleCommand;

class RoutesListCommand extends ConsoleCommand
{

	public function getName()
	{
		return 'routes:list';
	}

	public function getDescription()
	{
		return 'Prints asc list of routes';
	}

	public function execute(array $args)
	{
		$routes = $this->getApp()->getRoutingService()->getAllRoutes();
		ksort($routes);

		foreach ($routes as $key => $r) {
			$this->writeLn($key . $r['url'] . ':' . $r['controller']);
		}
	}

}