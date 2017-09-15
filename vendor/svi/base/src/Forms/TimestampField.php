<?php

namespace Svi\Base\Forms;

class TimestampField extends TextField
{

	public function getViewParameters()
	{
		return array_merge(parent::getViewParameters(), [
			'data' => $this->getData() ? date('d.m.Y' . ($this->getWithTime() ? ' H:i' : ''), $this->getData()) : null,
		]);
	}

	public function setData($value)
	{
        $value = trim($value);

	    if (!$value) {
	        parent::setData(null);
        } elseif (is_numeric($value)) {
			parent::setData($value);
		} else {
			parent::setData(
			    \DateTime::createFromFormat(
                        'd.m.Y H:i',
                        $value . ($this->getWithTime() ? '' : ' 00:00'),
                        new \DateTimeZone(date_default_timezone_get())
                    )->getTimestamp()
            );
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