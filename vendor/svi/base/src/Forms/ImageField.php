<?php

namespace Svi\Base\Forms;

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
		return @$this->parameters['minWidth'];
	}
	public function setMinWidth($value)
	{
		$this->parameters['minWidth'] = $value;
		return $this;
	}

	public function getMaxWidth()
	{
		return @$this->parameters['maxWidth'];
	}
	public function setMaxWidth($value)
	{
		$this->parameters['maxWidth'] = $value;
		return $this;
	}

	public function getMinHeight()
	{
		return @$this->parameters['minHeight'];
	}
	public function setMinHeight($value)
	{
		$this->parameters['minHeight'] = $value;
		return $this;
	}

	public function getMaxHeight()
	{
		return @$this->parameters['maxHeight'];
	}
	public function setMaxHeight($value)
	{
		$this->parameters['maxHeight'] = $value;
		return $this;
	}

	public function getImageSizeMessage()
	{
		return @$this->parameters['imageSizeMessage'];
	}
	public function setImageSizeMessage($value)
	{
		$this->parameters['imageSizeMessage'] = $value;
		return $this;
	}

} 