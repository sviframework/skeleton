<?php

namespace Svi\File\Classes;

class File
{
	private $uri;
	private $fileInfo;

	public function __construct($uri)
	{
		$this->setUri($uri);
	}

	/**
	 * Set uri
	 *
	 * @param string $uri
	 */
	public function setUri($uri)
	{
		$this->uri = $uri;
	}

	/**
	 * Get uri
	 *
	 * @return string
	 */
	public function getUri()
	{
		return $this->uri;
	}

	public function getUriDir()
	{
		return strtolower(pathinfo($this->getUri(), PATHINFO_DIRNAME));
	}

	public function getUrl()
	{
		return '/files/' . $this->getUri();
	}

	public function getInternalUrl()
	{
		return 'files/' . $this->getUri();
	}

	public function getExtension()
	{
		return strtolower(pathinfo($this->getUri(), PATHINFO_EXTENSION));
	}

	public function isImage()
	{
		if (in_array($this->getExtension(), array('png', 'jpg', 'jpeg', 'gif'))) {
			return true;
		}

		return false;
	}

	public function isJpeg()
	{
		return in_array($this->getExtension(), array('jpg', 'jpeg'));
	}

	public function isGif()
	{
		return $this->getExtension() == 'gif';
	}

	public function isPng()
	{
		return $this->getExtension() == 'png';
	}

	function __toString()
	{
		return $this->getUri();
	}

	protected function &getFileInfo()
	{
		if (!$this->fileInfo) {
			$fileSize = filesize($this->getInternalUrl());
			$this->fileInfo = array(
				'name' => pathinfo($this->getInternalUrl(), PATHINFO_BASENAME),
				'filesize' => $fileSize,
				'human_filesize' => $this->humanFilesize($fileSize),
			);
		}

		return $this->fileInfo;
	}

	public function getName()
	{
		$fileInfo = $this->getFileInfo();

		return $fileInfo['name'];
	}

	public function getSize()
	{
		$fileInfo = $this->getFileInfo();

		return $fileInfo['filesize'];
	}

	public function getHumanSize()
	{
		$fileInfo = $this->getFileInfo();

		return $fileInfo['human_filesize'];
	}

	protected function humanFilesize($bytes, $decimals = 2)
	{
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

}
