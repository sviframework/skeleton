<?php

namespace Svi;

trait BundleTrait
{

    protected function get($key)
    {
        if (isset($this->c)) {
            return $this->c->getApp()->get($key);
        } else {
            return $this->getApp()->get($key);
        }
    }

}