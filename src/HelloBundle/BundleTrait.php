<?php

namespace HelloBundle;

use HelloBundle\Service\HelloService;

trait BundleTrait
{
    /**
     * @return HelloService
     */
    public function getHelloService()
    {
        return $this->app[HelloService::class];
    }

}