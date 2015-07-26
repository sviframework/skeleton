<?php

namespace Svi\File\Controller;

use Svi\Base\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Svi\File\Classes\File;

abstract class TinyMceUploadController extends Controller
{

	public function imageAction(Request $request)
	{
		$type = $request->query->get('type');

		$form = $this->createForm()
			->add('upload', $type == 'file' ? 'file' : 'image', array(
				'label' => 'Файл',
				'required' => true,
			));

		$result = false;

		if ($form->handleRequest($request)->isValid()) {
			$file = new File($this->c->getFileManager()->uploadFile($form->get('upload')->getData(), 'uploaded_images/' . date('Ym')));
			$result = $file->getUrl();
		}

		return $this->render('image', array(
			'form' => $form,
			'result' => $result,
			'type' => $type,
		));
	}

}