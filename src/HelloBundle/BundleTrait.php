<?php

namespace HelloBundle;

use HelloBundle\Service\HelloService;

trait BundleTrait
{
    use \Svi\Service\BundlesService\BundleTrait;

    /**
     * @return HelloService
     */
    public function getHelloService()
    {
        return $this->get(HelloService::class);
    }

}