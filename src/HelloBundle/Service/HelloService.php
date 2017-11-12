<?php

namespace HelloBundle\Service;

use HelloBundle\BundleTrait;
use Svi\AppContainer;

class HelloService extends AppContainer
{
    use BundleTrait;

    public function getHelloText($toName)
    {
        return "Hello, $toName!";
    }

}