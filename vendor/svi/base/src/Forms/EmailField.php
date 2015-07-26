<?php

namespace Svi\Base\Forms;

use Svi\Base\Validators\Email;

class EmailField extends Field
{

	public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'inputType' => 'email',
		];
	}

	public function validateData()
	{
		parent::validateData();
		if (!$this->hasErrors()) {
			if (!Email::validate($this->getData())) {
				$this->addError('forms.emailError');
			}
		}
	}

} 