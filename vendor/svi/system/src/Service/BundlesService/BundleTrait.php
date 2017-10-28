<?php

namespace Svi\Service\BundlesService;

trait BundleTrait
{

    protected function get($key)
    {
        if (isset($this->c)) {
            return $this->c->getApp()[$key];
        } else {
            return $this->getApp()[$key];
        }
    }

}