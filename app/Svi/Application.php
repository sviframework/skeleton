<?php
/**
 * SVI framework version 0.2
 * by Valery Shibanov shibaon@gmail.com
 */
namespace Svi;

class Application
{
	private static $_instance;

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

	private function __construct(array $argv = null)
	{
		if ($argv) {
			$this->console = true;
		}

		umask(0000);
		$loader = require_once __DIR__ . '/../../vendor/autoload.php';
		$this->silex = new \Silex\Application();

		$loader->add('', __DIR__.'/../../src');

		require_once __DIR__.'/./Config.php';
		$this->config = Config::getInstance();
		$this->silex['debug'] = $this->config->get('debug');

		require_once __DIR__.'/./Logger.php';
		$this->logger = Logger::getInstance($this);

		if ($this->config->get('debug')) {
			\Symfony\Component\Debug\ErrorHandler::register(E_ALL, true);
			\Symfony\Component\Debug\ExceptionHandler::register(true);
		} else {
			set_error_handler([$this->logger, 'handleError'], E_ALL ^ E_NOTICE);
			$this->silex->error([$this->logger, 'handleException']);
		}

		if ($this->config->get('db')) {
			require_once __DIR__.'/./Entity.php';
			$this->silex->register(new \Silex\Provider\DoctrineServiceProvider(), [
				'db.options' => $this->config->get('db'),
			]);
			Entity::$connection = $this->silex['db'];
		}
		$this->initTwig();
		if (!$this->console) {
			$this->silex['session'] = $this->silex->share(function(){
				require_once __DIR__.'/./Session.php';
				return Session::getInstance($this);
			});

			$this->silex['cookies'] = $this->silex->share(function(){
				require_once __DIR__.'/./Cookies.php';
				return Cookies::getInstance($this);
			});
		}
		$this->silex['translation'] = $this->silex->share(function(){
			require_once __DIR__.'/./Translation.php';
			return Translation::getInstance($this);
		});

		require_once __DIR__ . '/./Bundles.php';
		$this->bundles = Bundles::getInstance($this);

		if (!$this->console) {
			require_once __DIR__.'/./Routing.php';

			$this->silex->register(new \Silex\Provider\ServiceControllerServiceProvider());
			$this->routing = Routing::getInstance($this);
		}

		if ($this->console) {
			require_once __DIR__.'/./Console.php';
			$this->console = Console::getInstance($this, $argv);
		}
	}

	protected function _run()
	{
		if (!$this->console) {
			$this->getSilex()->run();
		} else {
			$this->console->run();
		}
	}

	private function __clone(){}
	private function __wakeup(){}

	static public function run(array $argv = null)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($argv);
			self::$_instance->_run();
		} else {
			throw new \Exception('Application already run');
		}
	}

	public function initTwig()
	{
		if ($this->getConfig()->get('twig')) {
			$this->silex->register(new \Silex\Provider\TwigServiceProvider(), [
				'twig.path' => __DIR__.'/./../../src',
				'twig.options' => [
						'cache' => $this->silex['debug'] ? false : __DIR__.'/./../cache',
					] + $this->getConfig()->get('twig'),
			]);
			require_once __DIR__.'/./SilexTwigExtension.php';
			$this->getSilex()['twig']->addExtension(new SilexTwigExtension($this));
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
		return $this->silex[$service];
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->silex['request'];
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
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->silex['db'];
	}

	/**
	 * @return \Svi\Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	public function getRootDir()
	{
		return dirname(dirname(__DIR__));
	}

	/**
	 * @return \Twig_Environment
	 */
	public function getTwig()
	{
		return $this->get('twig');
	}

	public function isConsole()
	{
		return $this->console;
	}

}