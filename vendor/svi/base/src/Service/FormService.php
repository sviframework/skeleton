<?php
namespace Svi\Base\Service;

use Svi\Base\BundleTrait;
use Svi\Base\ContainerAware;
use Svi\Base\Forms\Form;

class FormService extends ContainerAware
{
    use BundleTrait;

	public function createForm(array $parameters = [])
	{
		return new Form($this->c, $parameters);
	}

} 