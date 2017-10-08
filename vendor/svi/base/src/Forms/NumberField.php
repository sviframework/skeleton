<?php

namespace Svi\Base\Forms;

class NumberField extends Field
{

    public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'inputType' => 'text',
		];
	}

    public function validateData()
    {
        parent::validateData();

        if (!$this->hasErrors() && $this->getData() !== '' && $this->getData() !== NULL) {
            if ($this->getDecimals()) {
                if (!is_numeric($this->getData())) {
                    $this->addError('forms.numberIsNotAFloat');
                }
            } else {
                if (!is_numeric($this->getData()) || !is_integer((int) $this->getData())) {
                    $this->addError('forms.numberIsNotAInteger');
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getDecimals()
    {
        return array_key_exists('decimals', $this->parameters) ? $this->parameters['decimals'] : 0;
    }

    /**
     * @param mixed $decimals
     * @return $this
     */
    public function setDecimals($decimals)
    {
        $this->parameters['decimals'] = $decimals;

        return $this;
    }

} 