<?php

namespace Svi\MailBundle;

use Svi\MailBundle\Service\MailService;

trait BundleTrait
{
    use \Svi\Service\BundlesService\BundleTrait;

    /**
     * @return MailService
     */
    public function getMailService()
    {
        return $this->get(MailService::class);
    }
}