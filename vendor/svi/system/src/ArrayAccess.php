<?php

namespace Svi;

class ArrayAccess implements \ArrayAccess
{
    private $values = [];

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet($offset)
    {
        $result = $this->values[$offset];

        if (is_callable($result)) {
            $this->values[$offset] = $result = $result();
        }

        return $result;
    }

    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }

}