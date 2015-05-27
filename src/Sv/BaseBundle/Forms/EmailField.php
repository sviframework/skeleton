<?php

namespace Sv\BaseBundle\Forms;

use Sv\BaseBundle\Validators\Email;

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