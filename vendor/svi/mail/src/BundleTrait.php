<?php

namespace Svi\Mail;

use Svi\Mail\Service\MailService;

trait BundleTrait
{
    use \Svi\BundleTrait;

    /**
     * @return MailService
     */
    public function getMailService()
    {
        return $this->get(MailService::class);
    }
}