<?php

namespace Svi\BaseBundle\Forms;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileField extends Field
{

	function __construct($name, array $parameters)
	{
		parent::__construct($name, $parameters);
	}


	public function getViewParameters()
	{
		return parent::getViewParameters() + [
			'inputType' => 'file',
		];
	}

	public function getTemplate()
	{
		return 'file';
	}

	public function setData($value)
	{
		if ($value && is_array($value) && isset($value['tmp_name']) && $value['tmp_name']) {
			$this->data = new UploadedFile($value['tmp_name'], $value['name']);
		}
		return $this;
	}

	public function validateData()
	{
		parent::validateData();
		if (!$this->hasErrors() && ($file = $this->getData())) {
			if ($file instanceof UploadedFile) {
				$maxSizeParse = $this->getMaxSize();
				if ($maxSizeParse) {
					if (preg_match('/^([0-9]+)k$/i', $maxSizeParse, $matches)) {
						$maxSizeParse = $matches[1] * 1024;
					} elseif (preg_match('/^([0-9]+)M$/i', $maxSizeParse, $matches)) {
						$maxSizeParse = $matches[1] * 1024 * 1024;
					} elseif (preg_match('/^([0-9]+)$/i', $maxSizeParse, $matches)) {
						$maxSizeParse = $matches[1];
					} else {
						throw new \Exception('Incorrect max size "' . $maxSizeParse . '" for field "' . $this->getName() . '"');
					}

					if ($file->getSize() > $maxSizeParse) {
						$this->addError($this->getMaxSizeMessage() ? $this->getMaxSizeMessage() : 'forms.maxSizeMessage');
					}
				}

				if (is_array($this->getMimeTypes())) {
					$types = array();
					foreach ($this->getMimeTypes() as $t) {
						$types[] = strtolower($t);
					}
					if (!in_array(strtolower($file->getMimeType()), $types)) {
						$this->addError($this->getMimeTypesMessage() ? $this->getMimeTypesMessage() : 'forms.mimeTypes');
					}
				}
			} else {
				$this->addError($this->getErrorMessage() ? $this->getErrorMessage() : 'forms.uploadError');
			}
		}
	}

	public function getMaxSize()
	{
		return isset($this->parameters['maxSize']) ? $this->parameters['maxSize'] : null;
	}
	public function setMaxSize($value)
	{
		$this->parameters['maxSize'] = $value;
	}

	public function getMimeTypes()
	{
		if (isset($this->parameters['mimeTypes'])) {
			if (is_string($this->parameters['mimeTypes'])) {
				return array($this->parameters['mimeTypes']);
			} elseif (is_array($this->parameters['mimeTypes'])) {
				return $this->parameters['mimeTypes'];
			}

			throw new \Exception('Incorrect mimeTypes value for "' . $this->getName() . '"');
		}

		return null;
	}
	public function setMimeTypes($value)
	{
		$this->parameters['mimeTypes'] = $value;
		return $this;
	}

	public function getMaxSizeMessage()
	{
		return isset($this->parameters['maxSizeMessage']) ? $this->parameters['maxSizeMessage'] : null;
	}
	public function setMaxSizeMessage($value)
	{
		$this->parameters['maxSizeMessage'] = $value;
		return $this;
	}

	public function getMimeTypesMessage()
	{
		return isset($this->parameters['mimeTypesMessage']) ? $this->parameters['mimeTypesMessage'] : null;
	}
	public function setMimeTypesMessage($value)
	{
		$this->parameters['mimeTypesMessage'] = $value;
		return $this;
	}

	public function getErrorMessage()
	{
		return isset($this->parameters['errorMessage']) ? $this->parameters['errorMessage'] : null;
	}
	public function setErrorMessage($value)
	{
		$this->parameters['errorMessage'] = $value;
		return $this;
	}


} 