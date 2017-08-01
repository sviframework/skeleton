<?php

namespace Svi;

use \Symfony\Component\Debug\ErrorHandler;
use \Symfony\Component\Debug\ExceptionHandler;
use \Silex\Provider\DoctrineServiceProvider;
use \Silex\Provider\ServiceControllerServiceProvider;
use \Silex\Provider\TwigServiceProvider;

class Application implements \ArrayAccess
{
	private $instanceId;

	/**
	 * @var Logger
	 */
	private $logger;

	private $console;
	/**
	 * @var \Silex\Application
	 */
	private $silex;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var Bundles
	 */
	private $bundles;

	/**
	 * @var Routing
	 */
	private $routing;

	/**
	 * @var TemplateProcessor
	 */
	private $templateProcessor;

	public function __construct($config = null, array $argv = null)
	{
		$this->instanceId = md5(time() . microtime() . rand());

		if ($argv) {
			$this->console = true;
		}

		umask(0000);
		$loader = require $this->getRootDir() . '/vendor/autoload.php';
		$this->silex = new \Silex\Application();

		$loader->add('', $this->getRootDir() . '/src');

		$this->config = new Config($this, $config);
		$this->silex['debug'] = $this->config->get('debug');

		$this->logger = new Logger($this);

		ErrorHandler::register();
		$handler = ExceptionHandler::register($this->config->get('debug'));
		$handler->setHandler([$this->logger, 'handleException']);

		if ($this->config->get('dbs')) {
			$this->silex->register(new DoctrineServiceProvider(), [
				'dbs.options' => $this->config->get('dbs'),
			]);
		}

		$this->templateProcessor = new TemplateProcessor($this);
		$this->tryInitTwig();

		if (!$this->console) {
			$this->silex['session'] = function(){
				return new Session($this);
			};

			$this->silex['cookies'] = function(){
				return new Cookies($this);
			};
		}
		$this->silex['translation'] = function(){
			return new Translation($this);
		};

		$this->bundles = new Bundles($this);

		if (!$this->console) {
			$this->silex->register(new ServiceControllerServiceProvider());
		}
		$this->routing = new Routing($this);

		if ($this->console) {
			$this->console = new Console($this, $argv);
		}
	}

	public function run()
	{
		if (!$this->console) {
			$this->getSilex()->run();
		} else {
			$this->console->run();
		}
	}

	public function tryInitTwig()
	{
		if ($this->getConfig()->get('twig')) {
			$this->silex->register(new TwigServiceProvider(), [
				'twig.path' => [
					$this->getRootDir() . '/src',
					$this->getRootDir() . '/vendor',
				],
				'twig.options' => [
					'cache' => $this->silex['debug'] ? false : $this->getRootDir() . '/app/cache',
				] + $this->getConfig()->get('twig'),
			]);
			$this->getSilex()['twig']->addExtension(new SilexTwigExtension($this));
			$this->getTemplateProcessor()->addProcessor('twig', $this->getSilex()['twig']);
		}
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @return Routing
	 */
	public function getRouting()
	{
		return $this->routing;
	}

	/**
	 * @return Bundles
	 */
	public function getBundles()
	{
		return $this->bundles;
	}

	/**
	 * @return \Silex\Application
	 */
	public function getSilex()
	{
		return $this->silex;
	}

	public function get($service)
	{
		if (!$this->offsetExists($service)) {
			throw new \Exception('Service "' . $service . '" is not registered');
		}

		return $this->offsetGet($service);
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->silex['request_stack']->getCurrentRequest();
	}

	/**
	 * @return Session
	 */
	public function getSession()
	{
		return $this->get('session');
	}

	/**
	 * @return Cookies
	 */
	public function getCookies()
	{
		return $this->get('cookies');
	}

	/**
	 * @return Translation
	 */
	public function getTranslation()
	{
		return $this->get('translation');
	}

	/**
	 * @param string $schemaName
	 * @return \Doctrine\DBAL\Connection
	 * @throws \Exception
	 */
	public function getDb($schemaName = 'default')
	{
		if (!$this->offsetExists('dbs')) {
			throw new \Exception('dbs is not configured');
		} elseif (!isset($this->silex['dbs'][$schemaName])) {
			throw new \Exception('dbs schema "' . $schemaName . '" is not configured');
		}

		return $this->silex['dbs'][$schemaName];
	}

	/**
	 * @return \Svi\Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * @return string Always returns current site dir with "/" in the end, so like /var/www/sample/www/sample.com/ will be returned
	 */
	public function getRootDir()
	{
		return dirname(dirname(dirname(dirname(__DIR__))));
	}

	/**
	 * @return TemplateProcessor
	 */
	public function getTemplateProcessor()
	{
		return $this->templateProcessor;
	}

	public function isConsole()
	{
		return $this->console;
	}

	public function offsetExists($offset)
	{
		return isset($this->silex[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->silex[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->silex[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->silex[$offset]);
	}

	public function getInstanceId()
	{
		return $this->instanceId;
	}

}