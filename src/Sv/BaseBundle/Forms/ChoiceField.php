<?php

namespace Sv\BaseBundle\Forms;

class ChoiceField extends Field
{

	public function getTemplate()
	{
		return 'choice';
	}

	public function getViewParameters()
	{
		$choices = $this->getRequired() ? [] : [null => ''];
		foreach ($this->getChoices() as $key => $value) {
			if ($key === 0) {
				$key = '0';
			} else {
				$key = '' . $key;
			}
			$choices[] = [
				'key' => $key,
				'value' => $value,
				'selected' => $key === $this->getData(),
			];
		}
		return parent::getViewParameters() + [
			'choices' => $choices,
		];
	}

	public function validateData()
	{
		parent::validateData();
		if (!$this->hasErrors()) {
			if (!array_key_exists($this->getData(), $this->getChoices())) {
				if ($this->getData() !== null && $this->getData() !== '') {
					$this->addError('forms.choicesIncorrect');
				}
			}
		}
	}


	public function getChoices()
	{
		return is_array(@$this->parameters['choices']) ? $this->parameters['choices'] : [];
	}
	public function setChoices(array $value = [])
	{
		$this->parameters['choices'] = $value;
	}

} 