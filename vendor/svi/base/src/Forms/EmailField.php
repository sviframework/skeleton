<?php

namespace Svi\BaseBundle\Forms;

use Svi\BaseBundle\Validators\Email;

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
			if ($this->getData() && !Email::validate($this->getData())) {
				$this->addError('forms.emailError');
			}
		}
	}

} 