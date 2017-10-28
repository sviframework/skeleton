<?php

namespace Svi\Service\HttpService;

use Svi\Application;
use Svi\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class Controller
{
	/**
	 * @var Container
	 */
	protected $c;

	function __construct(Application $app)
	{
		$this->c = Container::getInstance($app);
	}

	public function render($template, array $parameters = array())
	{
		if (strpos($template, '/') === false) {
			$parts = explode('\\', get_class($this));
			$lastPart = str_replace('Controller', '', $parts[count($parts) - 1]);
			unset($parts[count($parts) - 1]);
			unset($parts[count($parts) - 1]);
			$template = implode('/', $parts) . '/Views/' . $lastPart . '/' . $template;
		}

		return $this->c->getApp()->getTemplateService()->render($template, $parameters);
	}

	public function generateUrl($name, array $parameters = [], $absolute = false)
	{
		return $this->c->getRoutingService()->getUrl($name, $parameters, $absolute);
	}

	protected function getTemplateParameters(array $parameters = [])
	{
		return $parameters;
	}

	public function getParameter($key)
	{
		return $this->c->getApp()->getConfigService()->getParameter($key);
	}

	public function redirect($route, array $parameters = [])
	{
		return $this->redirectToUrl($this->generateUrl($route, $parameters));
	}

	public function redirectToUrl($url)
	{
		return new RedirectResponse($url);
	}

	public function getRequest()
	{
		return $this->c->getApp()->getRequest();
	}

	public function csrfCheck()
	{
		$referer = $this->getRequest()->headers->get('referer');

		if (strtolower($this->getRequest()->getHost()) != strtolower(parse_url($referer, PHP_URL_HOST))) {
			throw new \Exception('Csrf check failed');
		}
	}

}