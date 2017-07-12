<?php

namespace Svi;

class Routing
{
	/**
	 * @var Application
	 */
	private $app;
	private $routes = array();

	public function __construct(Application $app)
	{
		$this->app = $app;
		foreach ($this->app->getBundles()->getBundles() as $b) {
			$this->loadBundleRoutes($b, $b->getRoutes());
		}
	}

	public function loadBundleRoutes(Bundle $bundle, array $bundleRoutes)
	{
		$app = $this->app;
		foreach ($bundleRoutes as $controller => $routes) {
			$shareName = 'controller.' . $bundle->getName() . '.' . $controller;
			$className = $bundle->getNamespace() . '\\Controller\\' . $controller . 'Controller';
			$app->getSilex()[$shareName] = function() use ($app, $className) {
				return new $className($app);
			};
			foreach ($routes as $name => $route) {
				if (is_numeric($name)) {
					$name = '_route' . rand() . microtime(true);
				}
				if (is_string($route)) {
					$parts = explode(':', $route);
					$app->getSilex()->get($parts[0], $shareName . ':' . $parts[1] . 'Action');
					$app->getSilex()->post($parts[0], $shareName . ':' . $parts[1] . 'Action');
					$this->add($name, ['url' => $parts[0], 'controller' => $className . '::' . $parts[1]]);
				} elseif (is_array($route)) {
					$get = $app->getSilex()->get($route['route'], $shareName . ':' . $route['method'] . 'Action');
					$post = $app->getSilex()->post($route['route'], $shareName . ':' . $route['method'] . 'Action');
					if (@$route['requirements'] && count($route['requirements'])) {
						foreach ($route['requirements'] as $key => $value) {
							$get->assert($key, $value);
							$post->assert($key, $value);
						}
					}
					$this->add($name, ['url' => $route['route'], 'controller' => $className . '::' . $route['method']]);
				}
			}
		}
	}

	public function add($name, $route)
	{
		$this->routes[$name] = $route;
	}

	public function getUrl($name, array $parameters = null, $absolute = false, $protocol = null)
	{
		if (!($route = @$this->routes[$name]['url'])) {
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
