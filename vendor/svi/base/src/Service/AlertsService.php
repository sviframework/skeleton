<?php

namespace Svi\BaseBundle\Service;

use Svi\BaseBundle\BundleTrait;
use Svi\BaseBundle\ContainerAware;

class AlertsService extends ContainerAware
{
    use BundleTrait;

	public function addAlert($type, $text)
	{
		$alerts = $this->c->getSessionService()->get('alerts');
		if (!$alerts) {
			$alerts = [];
		}
		$alerts[] = ['type' => $type, 'text' => $text];

		$this->c->getSessionService()->set('alerts', $alerts);
	}

	public function getAlerts()
	{
		$alerts = $this->c->getSessionService()->get('alerts');
		$this->c->getSessionService()->uns('alerts');

		return $alerts;
	}

}
