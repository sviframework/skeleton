<?php

namespace Svi\Service;

use Svi\Application;
use Svi\Service\BundlesService\Bundle;

class RoutingService
{
	/**
	 * @var Application
	 */
	private $app;
	private $routes = array();

	public function __construct(Application $app)
	{
		$this->app = $app;
		foreach ($this->app->getBundlesService()->getBundles() as $b) {
			$this->loadBundleRoutes($b);
		}
	}

	public function loadBundleRoutes(Bundle $bundle)
	{
		$app = $this->app;
		foreach ($bundle->getRoutes() as $controller => $routes) {
			$className = $bundle->getNamespace() . '\\Controller\\' . $controller . 'Controller';
			$app[$className] = function() use ($app, $className) {
				return new $className($app);
			};
			foreach ($routes as $name => $route) {
				if (is_numeric($name)) {
					$name = '_route' . rand() . microtime(true);
				}
                $parts = explode(':', $route);
                $this->add($name, ['url' => $parts[0], 'controller' => $className, 'method' => $parts[1] . 'Action']);
			}
		}
	}

	public function add($name, $route)
	{
	    $route['regexp'] = '#^' . preg_replace('#({[A-z_]+[A-z0-9_]?})#', '([A-z0-9-_.]+)', $route['url']) . '$#';
		$this->routes[$name] = $route;
	}

	public function dispatchUrl($url)
    {
        $matches = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['regexp'], $url, $matches)) {
                array_shift($matches);
                $route['args'] = $matches;

                return $route;
            }
        }

        return false;
    }

	public function getUrl($name, array $parameters = null, $absolute = false, $protocol = null)
	{
		if (!($route = isset($this->routes[$name]) && isset($this->routes[$name]['url']) ? $this->routes[$name]['url'] : null)) {
			throw new \Exception('There is no route with name ' . $name);
		}
		if ($parameters) {
			foreach ($parameters as $key => $value) {
				$route = str_replace('{' . $key . '}', $value, $route, $count);
			}
		}
		$matches = array();
		if (preg_match('/(\{[A-z]+\})/', $route, $matches)) {
			throw new \Exception('You must provide argument ' . str_replace(['{', '}'], '', $matches[1]) . ' for route ' . $name);
		}
		if ($absolute) {
			$route = $this->app->getRequest()->getHttpHost() . $route;
			
			if (!in_array($protocol, ['https', 'http'])) {
				$protocol = $this->app->getRequest()->isSecure() ? 'https' : 'http';
			}
			$route = $protocol . '://' . $route;
		}

		return $route;
	}

	/**
	 * @return array
	 */
	public function getAllRoutes()
	{
		return $this->routes;
	}

} 
