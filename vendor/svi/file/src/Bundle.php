<?php

namespace Svi\File;

class Bundle extends \Svi\Bundle
{

	protected function getServices()
	{
		return [
			'manager.svifile' => 'FileManager',
			'manager.sviimage' => 'ImageManager',
		];
	}

} 