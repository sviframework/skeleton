<?php

namespace Svi\BaseBundle\Forms;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageField extends FileField
{

	function __construct($name, array $parameters)
	{
		parent::__construct($name, array_merge(['mimeTypes' => ['image/jpeg', 'image/gif', 'image/png']], $parameters));
	}

	public function validateData()
	{
		parent::validateData();
		if (!$this->hasErrors() && ($file = $this->getData())) {
			if ($file instanceof UploadedFile) {
				list($width, $height) = getimagesize($file->getPathname());
				if ($width < $this->getMinWidth()
					|| $this->getMaxWidth() && $width > $this->getMaxWidth()
					|| $height < $this->getMinHeight()
					|| $this->getMaxHeight() && $height > $this->getMaxHeight()
				) {
					$this->addError($this->getImageSizeMessage() ? $this->getImageSizeMessage() : 'forms.imageSizeMessage');
				}
			}
		}
	}

	public function getMinWidth()
	{
		return isset($this->parameters['minWidth']) ? $this->parameters['minWidth'] : null;
	}
	public function setMinWidth($value)
	{
		$this->parameters['minWidth'] = $value;
		return $this;
	}

	public function getMaxWidth()
	{
		return isset($this->parameters['maxWidth']) ? $this->parameters['maxWidth'] : null;
	}
	public function setMaxWidth($value)
	{
		$this->parameters['maxWidth'] = $value;
		return $this;
	}

	public function getMinHeight()
	{
		return isset($this->parameters['minHeight']) ? $this->parameters['minHeight'] : null;
	}
	public function setMinHeight($value)
	{
		$this->parameters['minHeight'] = $value;
		return $this;
	}

	public function getMaxHeight()
	{
		return isset($this->parameters['maxHeight']) ? $this->parameters['maxHeight'] : null;
	}
	public function setMaxHeight($value)
	{
		$this->parameters['maxHeight'] = $value;
		return $this;
	}

	public function getImageSizeMessage()
	{
		return isset($this->parameters['imageSizeMessage']) ? $this->parameters['imageSizeMessage'] : null;
	}
	public function setImageSizeMessage($value)
	{
		$this->parameters['imageSizeMessage'] = $value;
		return $this;
	}

} 