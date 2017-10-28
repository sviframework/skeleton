<?php

namespace Svi\BaseBundle\Forms;

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
		return isset($this->parameters['cancel']) ? $this->parameters['cancel'] : null;
	}
	public function setCancel($value)
	{
		$this->parameters['cancel'] = $value;
		return $this;
	}

} 