<?php

namespace Sv\BaseBundle\Forms;

class TextField extends Field
{

	public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'inputType' => 'text',
		];
	}

} 