<?php

namespace Sv\BaseBundle;

class AlertsManager extends ContainerAware
{

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
