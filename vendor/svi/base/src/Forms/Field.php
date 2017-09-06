<?php

namespace Svi\Base\Forms;

abstract class Field
{
    private $type;
    private $name;
    private $rendered;
    private $errors = array();
    protected $parameters;
    protected $data;

    function __construct($name, array $parameters)
    {
        $this->name = $name;
        $this->parameters = array_merge([
            'trim' => true,
        ], $parameters);
        if (array_key_exists('data', $parameters)) {
            $this->data = $parameters['data'];
        }
        if (!array_key_exists('attr', $this->parameters)) {
            $this->parameters['attr'] = [];
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return isset($this->parameters['label']) ? $this->parameters['label'] : $this->getName();
    }

    public function getViewParameters()
    {
        $attr = @$this->parameters['attr'];
        $class = @$attr['class'];
        if ($class) {
            unset($attr['class']);
        }
        if ($this->getReadOnly()) {
            $attr['readonly'] = 1;
        }
        if ($this->getDisabled()) {
            $attr['disabled'] = 1;
        }

        return [
            'id'          => $this->getName(),
            'name'        => $this->getName(),
            'label'       => $this->getLabel(),
            'required'    => $this->getRequired(),
            'placeholder' => $this->getPlaceholder(),
            'data'        => $this->getData(),
            'errors'      => $this->hasErrors() ? $this->getErrors() : null,
            'help'        => $this->getHelp(),
            'class'       => $class,
            'attr'        => $attr,
            'before'      => isset($this->parameters['before']) ? $this->parameters['before'] : null,
            'after'       => isset($this->parameters['after']) ? $this->parameters['after'] : null,
        ];
    }

    public function getAttr()
    {
        return $this->parameters['attr'];
    }

    public function setAttr(array $value)
    {
        $this->parameters['attr'] = $value;

        return $this;
    }

    public function getTrim()
    {
        return $this->parameters['trim'];
    }

    public function setTrim($value)
    {
        $this->parameters['trim'] = $value;

        return $this;
    }

    final public function getType()
    {
        if (empty($this->type)) {
            $className = get_class($this);
            $strpos = strrpos($className, '\\');
            $className = substr($className, $strpos + 1, strlen($className) - $strpos - 1);
            $this->type = strtolower(substr($className, 0, strrpos($className, 'Field')));
        }

        return $this->type;
    }

    /**
     * @return boolean
     */
    public function getRequired()
    {
        return @$this->parameters['required'] ? true : false;
    }

    public function setRequired($value)
    {
        $this->parameters['required'] = $value;

        return $this;
    }

    public function isRequired()
    {
        return $this->getRequired();
    }

    public function getRequiredMessage()
    {
        return @$this->parameters['requiredMessage'];
    }

    public function setRequiredMessage($value)
    {
        $this->parameters['requiredMessage'] = $value;

        return $this;
    }

    public function getHelp()
    {
        return @$this->parameters['help'];
    }

    public function setHelp($value)
    {
        $this->parameters['help'] = $value;

        return $this;
    }

    public function isReadOnly()
    {
        return $this->isDisabled() || $this->getReadOnly();
    }

    public function getReadOnly()
    {
        return @$this->parameters['readOnly'];
    }

    public function setReadOnly($value)
    {
        $this->parameters['readOnly'] = $value;

        return $this;
    }

    public function isDisabled()
    {
        return @$this->parameters['disabled'];
    }

    public function getDisabled()
    {
        return @$this->parameters['disabled'];
    }

    public function setDisabled($value)
    {
        $this->parameters['disabled'] = $value;

        return $this;
    }

    public function getTemplate()
    {
        return @$this->parameters['template'];
    }

    public function setTemplate($value)
    {
        $this->parameters['template'] = $value;

        return $this;
    }

    public function getPlaceholder()
    {
        return @$this->parameters['placeholder'];
    }

    public function getData()
    {
        return $this->data === '' ? null : $this->data;
    }

    public function setData($value)
    {
        if ($this->getTrim()) {
            $value = trim($value);
        }
        $this->data = $value;

        return $this;
    }

    public function validateData()
    {
        if ($this->getRequired() && ($this->getData() === '' || $this->getData() === null)) {
            $this->addError($this->getRequiredMessage() ? $this->getRequiredMessage() : 'forms.requiredError');
        }
    }

    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    public function isValid()
    {
        return !$this->hasErrors();
    }

    public function getRendered()
    {
        return $this->rendered;
    }

    public function setRendered($rendered)
    {
        $this->rendered = $rendered;

        return $this;
    }

    public function isRequireSubmit()
    {
        return !$this->isReadOnly();
    }

    public function isNotInput()
    {
        return false;
    }

} 