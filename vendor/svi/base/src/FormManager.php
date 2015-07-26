<?php
namespace Svi\Base;

use Svi\Base\Forms\Form;

class FormManager extends ContainerAware
{

	public function createForm(array $parameters = [])
	{
		return new Form($this->c, $parameters);
	}

} 