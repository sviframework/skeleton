<?php

namespace Svi\BaseBundle\Forms;

class TextareaField extends Field
{

	public function getTemplate()
	{
		return parent::getTemplate() ? parent::getTemplate() : 'textarea';
	}

} 