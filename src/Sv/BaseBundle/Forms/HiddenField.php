<?php

namespace Sv\BaseBundle\Forms;

class HiddenField extends Field
{

	public function getTemplate()
	{
		return parent::getTemplate() ? parent::getTemplate() : 'hidden';
	}

} 