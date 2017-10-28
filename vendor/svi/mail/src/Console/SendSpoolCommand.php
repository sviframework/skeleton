<?php

namespace Svi\MailBundle\Console;

use Svi\MailBundle\Service\MailService;
use Svi\Service\ConsoleService\ConsoleCommand;

class SendSpoolCommand extends ConsoleCommand
{
    public function getName()
    {
        return 'mail:send-spool';
    }

    public function getDescription()
    {
        return 'Sends mail messages from spool';
    }

    public function execute(array $args)
    {
        $this->getApp()[MailService::class]->sendSpool();
    }

} 