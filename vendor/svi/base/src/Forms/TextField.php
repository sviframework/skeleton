<?php

namespace Svi\BaseBundle\Forms;

class TextField extends Field
{

	public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'inputType' => 'text',
		];
	}

} 