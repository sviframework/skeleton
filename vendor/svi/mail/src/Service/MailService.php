<?php

namespace Svi\MailBundle\Service;

use Svi\Application;
use Svi\BaseBundle\ContainerAware;

class MailService extends ContainerAware
{
	private $swift = null;

	function __construct(Application $app)
	{
		parent::__construct($app);

		$this->getSwift();
	}

	public function sendSpool()
	{
		if (!$this->c->getConfigService()->getParameter('mail.spool')) {
			throw new \Exception('No mail.spool dir configured');
		}
		$spool = new \Swift_FileSpool($this->c->getApp()->getRootDir() . '/' . $this->c->getConfigService()->getParameter('mail.spool'));
		if ($this->c->getConfigService()->getParameter('mail.spoolTimeLimit')) {
			$spool->setTimeLimit($this->c->getConfigService()->getParameter('mail.spoolTimeLimit'));
		}
		if ($this->c->getConfigService()->getParameter('mail.spoolMessageLimit')) {
			$spool->setMessageLimit($this->c->getConfigService()->getParameter('mail.spoolMessageLimit'));
		}

		$spool->flushQueue($this->getRealTransport());
	}

	protected function swiftMail(\Swift_Message $message)
	{
		$this->getSwift()->send($message);
	}

	/**
	 * @return \Swift_Mailer
	 * @throws \Exception
	 */
	private function getSwift()
	{
		if (!$this->swift) {
			$transport = null;

			if ($this->c->getConfigService()->getParameter('mail.spool')) {
				$spool = new \Swift_FileSpool($this->c->getApp()->getRootDir() . '/' . $this->c->getConfigService()->getParameter('mail.spool'));
				$transport = new \Swift_SpoolTransport($spool);
			} else {
				$transport = $this->getRealTransport();
			}

			$this->swift = new \Swift_Mailer($transport);
		}

		return $this->swift;
	}

	private function getRealTransport()
	{
		switch ($this->c->getConfigService()->getParameter('mail.transport')) {
			case 'mail':
				$transport = new \Swift_SendmailTransport();
				break;
			case 'smtp':
				if (!$this->c->getConfigService()->getParameter('mail.host')) {
					throw new \Exception('No mail.host defined for smtp in config');
				}
				if (!$this->c->getConfigService()->getParameter('mail.port')) {
					throw new \Exception('No mail.port defined for smtp in config');
				}
				$transport = new \Swift_SmtpTransport(
					$this->c->getConfigService()->getParameter('mail.host'),
					$this->c->getConfigService()->getParameter('mail.port'),
					$this->c->getConfigService()->getParameter('mail.encryption')
				);
				$transport
					->setUsername($this->c->getConfigService()->getParameter('mail.user'))
					->setPassword($this->c->getConfigService()->getParameter('mail.password'));
				break;
		}

		if (!$transport) {
			throw new \Exception('No correct mail.transport defined in config (correct are: mail, smtp)');
		}

		return $transport;
	}

}