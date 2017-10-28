<?php

namespace Svi;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Svi\Service\BundlesService;
use Svi\Service\ConfigService;
use Svi\Service\ConsoleService;
use Svi\Service\CookiesService;
use Svi\Service\ExceptionService;
use Svi\Service\HttpService;
use Svi\Service\LoggerService;
use Svi\Service\RoutingService;
use Svi\Service\SessionService;
use Svi\Service\TemplateService;
use Svi\Service\TranslationService;

class Application extends ArrayAccess
{
    private $instanceId;

    public function __construct($config = null, array $argv = null)
    {
        $this->instanceId = md5(time() . microtime() . rand());

        $this['console'] = !!$argv;

        umask(0000);
        $loader = require $this->getRootDir() . '/vendor/autoload.php';

        $loader->add('', $this->getRootDir() . '/src');

        $this[ConfigService::class] = new ConfigService($this, $config);
        $this['debug'] = $this->getConfigService()->get('debug');
        $this[LoggerService::class] = new LoggerService($this);

        $this[ExceptionService::class] = new ExceptionService($this);

        if ($this->getConfigService()->get('dbs')) {
            $this['dbs'] = new ArrayAccess();
            foreach ($this->getConfigService()->get('dbs') as $name => $db) {
                $this['dbs'][$name] = DriverManager::getConnection($db, new Configuration());
            }
        }

        $this[TemplateService::class] = function () {
            return new TemplateService($this);
        };

        if (!$this->isConsole()) {
            $this[SessionService::class] = function(){
                return new SessionService($this);
            };

            $this[CookiesService::class] = function(){
                return new CookiesService($this);
            };
        }
        $this[TranslationService::class] = function(){
            return new TranslationService($this);
        };

        if (!$this->isConsole()) {
            $this[HttpService::class] = new HttpService($this);
        }

        $this[BundlesService::class] = new BundlesService($this);
        $this[RoutingService::class] = new RoutingService($this);

        if ($this->isConsole()) {
            $this[ConsoleService::class] = new ConsoleService($this, $argv);
        }
    }

    public function run()
    {
        if (!$this->isConsole()) {
            $this[HttpService::class]->run();
        } else {
            $this[ConsoleService::class]->run();
        }
    }

    /**
     * @return ConfigService
     */
    public function getConfigService()
    {
        return $this[ConfigService::class];
    }

    /**
     * @return RoutingService
     */
    public function getRoutingService()
    {
        return $this[RoutingService::class];
    }

    /**
     * @return BundlesService
     */
    public function getBundlesService()
    {
        return $this[BundlesService::class];
    }

    /**
     * @return HttpService
     */
    public function getHttpService()
    {
        return $this[HttpService::class];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->getHttpService()->getRequest();
    }

    /**
     * @return SessionService
     */
    public function getSessionService()
    {
        return $this[SessionService::class];
    }

    /**
     * @return CookiesService
     */
    public function getCookiesService()
    {
        return $this[CookiesService::class];
    }

    /**
     * @return TranslationService
     */
    public function getTranslationService()
    {
        return $this[TranslationService::class];
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
        } elseif (!isset($this['dbs'][$schemaName])) {
            throw new \Exception('dbs schema "' . $schemaName . '" is not configured');
        }

        return $this['dbs'][$schemaName];
    }

    /**
     * @return LoggerService
     */
    public function getLogger()
    {
        return $this[LoggerService::class];
    }

    /**
     * @return string Always returns current site dir with "/" in the end, so like /var/www/sample/www/sample.com/ will be returned
     */
    public function getRootDir()
    {
        return dirname(dirname(dirname(dirname(__DIR__))));
    }

    /**
     * @return TemplateService
     */
    public function getTemplateService()
    {
        return $this[TemplateService::class];
    }

    public function isConsole()
    {
        return $this['console'];
    }

    public function getInstanceId()
    {
        return $this->instanceId;
    }

    public function before($callback, $prepend = false)
    {
        if (!$this->isConsole()) {
            $this->getHttpService()->before($callback, $prepend);
        }
    }

    public function after($callback, $prepend = false)
    {
        if (!$this->isConsole()) {
            $this->getHttpService()->after($callback, $prepend);
        }
    }

    public function finish($callback, $prepend = false)
    {
        if (!$this->isConsole()) {
            $this->getHttpService()->finish($callback, $prepend);
        }
    }

    public function error($callback, $prepend = false)
    {
        $this[ExceptionService::class]->error($callback, $prepend);
    }

}