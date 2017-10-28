<?php

namespace Svi\FileBundle\Controller;

use Svi\BaseBundle\Controller\Controller;
use Svi\FileBundle\BundleTrait;
use Symfony\Component\HttpFoundation\Request;
use Svi\FileBundle\Classes\File;

abstract class TinyMceUploadController extends Controller
{
    use BundleTrait;

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
			$file = new File($this->getFileService()
				->uploadFile($form->get('upload')->getData(), 'uploaded_images/' . date('Ym')));
			$result = $file->getUrl();
		}

		return $this->render('svi/file/src/Views/TinyMceUpload/image', array(
			'form' => $form,
			'result' => $result,
			'type' => $type,
		));
	}

}
