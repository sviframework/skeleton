<?php

namespace Sv\BaseBundle\Forms;

class TimestampField extends TextField
{

	public function getViewParameters()
	{
		return array_merge(parent::getViewParameters(), [
			'data' => date('d.m.Y' . ($this->getWithTime() ? ' H:i' : ''), $this->getData()),
		]);
	}

	public function setData($value)
	{
		if (is_numeric($value)) {
			parent::setData($value);
		} else {
			parent::setData(\DateTime::createFromFormat('d.m.Y' . ($this->getWithTime() ? ' H:i' : ''), $value)->getTimestamp());
		}
	}

	public function setWithTime($value)
	{
		$this->parameters['withTime'] = $value;

		return $this;
	}

	public function getWithTime()
	{
		return @$this->parameters['withTime'];
	}

} 