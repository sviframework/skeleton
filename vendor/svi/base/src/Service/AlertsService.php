<?php

namespace Svi\Base\Service;

use Svi\Base\BundleTrait;
use Svi\Base\ContainerAware;

class AlertsService extends ContainerAware
{
    use BundleTrait;

	public function addAlert($type, $text)
	{
		$alerts = $this->c->getSession()->get('alerts');
		if (!$alerts) {
			$alerts = [];
		}
		$alerts[] = ['type' => $type, 'text' => $text];

		$this->c->getSession()->set('alerts', $alerts);
	}

	public function getAlerts()
	{
		$alerts = $this->c->getSession()->get('alerts');
		$this->c->getSession()->uns('alerts');

		return $alerts;
	}

}
