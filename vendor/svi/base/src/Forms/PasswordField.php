<?php

namespace Svi\Base\Forms;

class PasswordField extends Field
{

	public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'inputType' => 'password',
		];
	}

} 