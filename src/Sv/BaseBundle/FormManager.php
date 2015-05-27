<?php
namespace Sv\BaseBundle;

use Sv\BaseBundle\Forms\Form;

class FormManager extends ContainerAware
{

	public function createForm(array $parameters = [])
	{
		return new Form($this->c, $parameters);
	}

} 