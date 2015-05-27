<?php

namespace Sv\FileBundle;

class Bundle extends \Svi\Bundle
{

	protected function getManagers()
	{
		return [
			'file' => 'File',
			'image' => 'Image',
		];
	}

	public function getRoutes()
	{
		return [
			'TinyMceUpload' => [
				'/admin/56eb28693ab23b9d53bd0792ad47ca8c:image',
			],
		];
	}

} 