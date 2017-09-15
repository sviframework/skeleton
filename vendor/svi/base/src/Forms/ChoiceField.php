<?php

namespace Svi\Base\Forms;

use Svi\Base\Entity\SelectableInterface;

class ChoiceField extends Field
{

	public function getTemplate()
	{
		return 'choice';
	}

	public function getChoicesForRender()
	{
		$choices = $this->getRequired() ? [] : [null => ''];
		foreach ($this->getChoices() as $key => $value) {
			if ($value instanceof SelectableInterface) {
				$key = $value->getKey();
				$value = $value->getValue();
			} else {
				if ($key === 0) {
					$key = '0';
				} else {
					$key = '' . $key;
				}
			}

			$choices[$key] = [
				'key' => $key,
				'value' => $value,
				'selected' => is_array($this->getData()) ? false : (string)$key === (string)$this->getData(),
			];
		}

		return $choices;
	}

	public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'choices' => $this->getChoicesForRender(),
		];
	}

	public function validateData()
	{
		parent::validateData();
		if (!$this->hasErrors()) {
			if (!array_key_exists($this->getData(), $this->getChoicesForRender())) {
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