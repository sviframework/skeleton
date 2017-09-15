<?php

namespace Svi\Mail\Service;

use Svi\Application;
use Svi\Base\Container;
use Svi\Base\ContainerAware;

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
		if (!$this->c->getConfig()->getParameter('mail.spool')) {
			throw new \Exception('No mail.spool dir configured');
		}
		$spool = new \Swift_FileSpool($this->c->getApp()->getRootDir() . '/' . $this->c->getConfig()->getParameter('mail.spool'));
		if ($this->c->getConfig()->getParameter('mail.spoolTimeLimit')) {
			$spool->setTimeLimit($this->c->getConfig()->getParameter('mail.spoolTimeLimit'));
		}
		if ($this->c->getConfig()->getParameter('mail.spoolMessageLimit')) {
			$spool->setMessageLimit($this->c->getConfig()->getParameter('mail.spoolMessageLimit'));
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

			if ($this->c->getConfig()->getParameter('mail.spool')) {
				$spool = new \Swift_FileSpool($this->c->getApp()->getRootDir() . '/' . $this->c->getConfig()->getParameter('mail.spool'));
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
		switch ($this->c->getConfig()->getParameter('mail.transport')) {
			case 'mail':
				$transport = new \Swift_SendmailTransport();
				break;
			case 'smtp':
				if (!$this->c->getConfig()->getParameter('mail.host')) {
					throw new \Exception('No mail.host defined for smtp in config');
				}
				if (!$this->c->getConfig()->getParameter('mail.port')) {
					throw new \Exception('No mail.port defined for smtp in config');
				}
				$transport = new \Swift_SmtpTransport(
					$this->c->getConfig()->getParameter('mail.host'),
					$this->c->getConfig()->getParameter('mail.port'),
					$this->c->getConfig()->getParameter('mail.encryption')
				);
				$transport
					->setUsername($this->c->getConfig()->getParameter('mail.user'))
					->setPassword($this->c->getConfig()->getParameter('mail.password'));
				break;
		}

		if (!$transport) {
			throw new \Exception('No correct mail.transport defined in config (correct are: mail, smtp)');
		}

		return $transport;
	}

} 