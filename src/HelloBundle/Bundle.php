<?php

namespace HelloBundle;

use HelloBundle\Service\HelloService;

class Bundle extends \Svi\Service\BundlesService\Bundle
{
    use BundleTrait;

    public function getRoutes()
    {
        return [
            'Index' => [
                '_index' => '/:index',
            ],
        ];
    }

    protected function getServices()
    {
        return [
            HelloService::class,
        ];
    }

}