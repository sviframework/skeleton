<?php

namespace HelloBundle\Controller;

use HelloBundle\BundleTrait;
use Svi\HttpBundle\Controller\Controller;

class IndexController extends Controller
{
    use BundleTrait;

    public function indexAction()
    {
        return $this->render('index', $this->getTemplateParameters([
            'helloText' => $this->getHelloService()->getHelloText('world'),
        ]));
    }

}