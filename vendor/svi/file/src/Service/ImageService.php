<?php

namespace Svi\File\Service;

use Svi\Base\ContainerAware;
use Svi\File\Classes\File;

class ImageService extends ContainerAware
{

	/**
	 * @param $fileOrUri
	 * @param $width
	 * @param $height
	 * @param string $mode
	 *
	 * Codes:
	 * 0 - scale and crop
	 * 1 - scale proportional and fill background by edges
	 *
	 * @return mixed|null
	 */
	public function getImagePath($fileOrUri, $width, $height, $mode = 0)
	{
		if (is_string($fileOrUri)) {
			$fileOrUri = new File($fileOrUri);
		}
		if (!file_exists($fileOrUri->getInternalUrl())) {
			return NULL;
		}
		$filename = $this->getImageFilename($fileOrUri, $width, $height, $mode);
		if (!file_exists($filename)) {
			$this->generateImage($fileOrUri, $width, $height, $mode);
		}

		return str_replace($this->c->getApp()->getRootDir() . '/web', '', $filename);
	}

	public function clearCache($fileOrUri)
	{
		if (is_string($fileOrUri)) {
			$fileOrUri = new File($fileOrUri, $this->c->getApp()->getRootDir() . '/web/files');
		}

		$files = glob($this->getImageDir($fileOrUri) . $fileOrUri->getName(true) . '_*');
		foreach ($files as $f) {
			unlink($f);
		}
	}

	protected function generateImage(File $file, $width, $height, $mode = 0)
	{
		$mime = mime_content_type($this->c->getApp()->getRootDir() . '/web/files/' . $file->getUri());
		if (!in_array(strtolower($mime), array('image/jpeg', 'image/gif', 'image/png'))) {
			throw new \Exception('File with path ' . $file->getUri() . ' is not an image.');
		}

		$imageDir = $this->getImageDir($file);
		if (!file_exists($imageDir)) {
			if (!mkdir($imageDir, 0777, true)) {
				throw new \Exception('Cannot create ' . $imageDir . ' directory');
			}
		}
		$filename = $this->getImageFilename($file, $width, $height, $mode);

		$this->resizeImage($file, $width, $height, $filename, $mode);
	}

	protected function resizeImage(File $file, $width, $height, $filename, $mode = 0)
	{
		$mime = mime_content_type($this->c->getApp()->getRootDir() . '/web/files/' . $file->getUri());

		switch (strtolower($mime)) {
			case 'image/jpeg':
				$resource = imagecreatefromjpeg($file->getInternalUrl());
				break;
			case 'image/gif':
				$resource = imagecreatefromgif($file->getInternalUrl());
				break;
			case 'image/png':
				$resource = imagecreatefrompng($file->getInternalUrl());
				break;
		}
		if (!isset($resource) || !$resource) {
			throw new \Exception('File ' . $file->getUrl() . ' is not an image.');
		}

		$initWidth = imagesx($resource);
		$initHeight = imagesy($resource);

		if ($mode == 1) { // scale proportional
			$srcRect = array('x' => 0, 'y' => 0, 'width' => $initWidth, 'height' => $initHeight);
			if ($initWidth/$initHeight == $width/$height) {
				$dstRect = array('x' => 0, 'y' => 0, 'width' => $width, 'height' => $height);
			} else if ($initWidth/$initHeight > $width/$height) {
				$rectHeight = round(($initHeight * $width) / $initWidth);
				$dstRect = array('x' => 0, 'y' => round($height / 2 - $rectHeight / 2), 'width' => $width, 'height' => $rectHeight);
			} else {
				$rectWidth = round(($initWidth * $height) / $initHeight);
				$dstRect = array('x' => round($width / 2 - $rectWidth / 2), 'y' => 0, 'width' => $rectWidth, 'height' => $height);
			}
		} else {
			$dstRect = array('x' => 0, 'y' => 0, 'width' => $width, 'height' => $height);
			if ($initWidth/$initHeight == $width/$height) {
				$srcRect = array('x' => 0, 'y' => 0, 'width' => $initWidth, 'height' => $initHeight);
			} else if ($initWidth/$initHeight > $width/$height) {
				$rectWidth = round(($width * $initHeight) / $height);
				$srcRect = array('x' => round($initWidth / 2 - $rectWidth / 2), 'y' => 0, 'width' => $rectWidth, 'height' => $initHeight);
			} else {
				$rectHeight = round(($initWidth * $height) / $width);
				$srcRect = array('x' => 0, 'y' => round($initHeight / 2 - $rectHeight / 2), 'width' => $initWidth, 'height' => $rectHeight);
			}
		}

		$newImage = imagecreatetruecolor($width, $height);
		imagecopyresampled($newImage, $resource, $dstRect['x'], $dstRect['y'], $srcRect['x'], $srcRect['y'], $dstRect['width'], $dstRect['height'], $srcRect['width'], $srcRect['height']);
		imagedestroy($resource);
		if ($mode == 1) {
			if ($initWidth/$initHeight == $width/$height) {

			} else if ($initWidth/$initHeight > $width/$height) {
				$this->fillBackgroundResampledVertical($newImage, $dstRect);
			} else {
				$this->fillBackgroundResampledHorisontal($newImage, $dstRect);
			}
		}

		if ($file->isJpeg()) {
			imagejpeg($newImage, $filename);
		} else if ($file->isGif()) {
			imagegif($newImage, $filename);
		} else if ($file->isPng()) {
			imagepng($newImage, $filename, 9);
		}
	}

	protected function fillBackgroundResampledVertical($image, $rect)
	{
		$height = imagesy($image);

		for ($n = $rect['y']; $n > 0; $n--) {
			imagecopy($image, $image, 0, $n - 1, 0, $n, $rect['width'], 1);
		}
		for ($n = $rect['y'] + $rect['height'] - 1; $n < $height; $n++) {
			imagecopy($image, $image, 0, $n + 1, 0, $n, $rect['width'], 1);
		}
	}

	protected function fillBackgroundResampledHorisontal($image, $rect)
	{
		$width = imagesx($image);

		for ($n = $rect['x']; $n > 0; $n--) {
			imagecopy($image, $image, $n - 1, 0, $n, 0, 1, $rect['height']);
		}
		for ($n = $rect['x'] + $rect['width'] - 1; $n < $width; $n++) {
			imagecopy($image, $image, $n + 1, 0, $n, 0, 1, $rect['height']);
		}
	}

	protected function getImageDir(File $file)
	{
		return $this->c->getApp()->getRootDir() . '/web/files/image/' . $file->getUriDir() . '/';
	}

	protected function getImageFilename(File $file, $width, $height, $mode = 0)
	{
		return $this->getImageDir($file) . $file->getName(true) . '_' . $width . '_' . $height . ($mode ? '_' . $mode : '') . '.' . $file->getExtension();
	}

}
