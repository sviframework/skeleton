<?php
namespace Svi\Base\Service;

use Svi\Base\ContainerAware;
use Svi\Base\Forms\Form;

class FormService extends ContainerAware
{

	public function createForm(array $parameters = [])
	{
		return new Form($this->c, $parameters);
	}

} 