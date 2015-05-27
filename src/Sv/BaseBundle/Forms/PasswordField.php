<?php

namespace Sv\BaseBundle\Forms;

class PasswordField extends Field
{

	public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'inputType' => 'password',
		];
	}

} 