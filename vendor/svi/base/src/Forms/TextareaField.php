<?php

namespace Svi\Base\Forms;

class TextareaField extends Field
{

	public function getTemplate()
	{
		return parent::getTemplate() ? parent::getTemplate() : 'textarea';
	}

} 