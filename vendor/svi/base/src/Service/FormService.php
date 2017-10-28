<?php
namespace Svi\BaseBundle\Service;

use Svi\BaseBundle\BundleTrait;
use Svi\BaseBundle\ContainerAware;
use Svi\BaseBundle\Forms\Form;

class FormService extends ContainerAware
{
    use BundleTrait;

	public function createForm(array $parameters = [])
	{
		return new Form($this->c, $parameters);
	}

} 