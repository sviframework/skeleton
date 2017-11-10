<?php

namespace HelloBundle\Service;

use HelloBundle\BundleTrait;
use Svi\ContainerAware;

class HelloService extends ContainerAware
{
    use BundleTrait;

    public function getHelloText($toName)
    {
        return "Hello, $toName!";
    }

}