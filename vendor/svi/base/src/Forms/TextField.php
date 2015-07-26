<?php

namespace Svi\Base\Forms;

class TextField extends Field
{

	public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'inputType' => 'text',
		];
	}

} 