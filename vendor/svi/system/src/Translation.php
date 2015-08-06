<?php

namespace Svi;

class Translation
{
	private static $_instance;
	/**
	 * @var Application
	 */
	private $app;
	private $locale;
	private $translations;

	private function __construct(Application $app)
	{
		$this->app = $app;
		$this->locale = strtolower($app->getConfig()->get('locale'));
	}

	private function __clone() {}
	private function __wakeup(){}

	public static function getInstance(Application $app)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($app);
		}

		return self::$_instance;
	}

	public function trans($string, array $params = [])
	{
		$this->loadTranslations();

		$result = @$this->translations[$string];
		if ($result === null) {
			$result = $string;
		}
		return count($params) ? str_replace(array_keys($params), $params, $result) : $result;
	}

	protected function loadTranslations()
	{
		if (isset($this->translations)) {
			return null;
		}

		if (!$this->app->getConfig()->get('debug')) {
			$cacheFile = $this->app->getRootDir().'/app/cache/translations_' . $this->locale . '.php';
			if (file_exists($cacheFile)) {
				$this->translations = include $cacheFile;
			} else {
				$this->translations = $this->getTranslationsFromBundles();
				$cache = "<?php\n return [\n";
				foreach ($this->translations as $key => $value) {
					$cache .= "'$key' => '" . str_replace(array('\\', "'"), array('\\\\', "\\'"), $value) . "',\n";
				}
				$cache .= '];';
				$file =fopen($cacheFile, 'w');
				fwrite($file, $cache);
				fclose($file);
			}
		} else {
			$this->translations = $this->getTranslationsFromBundles();
		}

		return null;
	}

	protected function getTranslationsFromBundles()
	{
		$result = [];
		$translations = [];
		/** @var Bundle $b */
		foreach ($this->app->getBundles()->getBundles() as $b) {
			$translations = array_merge($translations, $b->getTranslations($this->locale));
		}
		$getPairs = function($key, array $value) use (&$getPairs) {
			$result = array();
			foreach ($value as $k => $v) {
				$k = $key . '.' . $k;
				if (!is_array($v)) {
					$result[$k] = $v;
				} else {
					$result = array_merge($result, $getPairs($k, $v));
				}
			}

			return $result;
		};
		foreach ($translations as $key => $value) {
			if (!is_array($value)) {
				$result[$key] = $value;
			} else {
				$result = array_merge($result, $getPairs($key, $value));
			}
		}

		return $result;
	}

} 