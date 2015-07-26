<?php

namespace Svi\File;

class Bundle extends \Svi\Bundle
{

	protected function getManagers()
	{
		return [
			'svifile' => 'File',
			'sviimage' => 'Image',
		];
	}

} 