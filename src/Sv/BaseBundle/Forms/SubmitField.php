<?php

namespace Sv\BaseBundle\Forms;

class SubmitField extends Field
{

	public function getTemplate()
	{
		return parent::getTemplate() ? parent::getTemplate() : 'submit';
	}

	public function isRequireSubmit()
	{
		return false;
	}

	public function getViewParameters($formName = null)
	{
		return array_merge(parent::getViewParameters($formName), [
			'cancel' => $this->getCancel(),
		]);
	}

	public function isNotInput()
	{
		return true;
	}

	public function getCancel()
	{
		return @$this->parameters['cancel'];
	}
	public function setCancel($value)
	{
		$this->parameters['cancel'] = $value;
		return $this;
	}

} 