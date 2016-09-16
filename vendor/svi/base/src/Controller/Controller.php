<?php

namespace Svi\Base\Controller;

use Svi\Base\Container;
use Svi\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class Controller extends \Svi\Controller
{
	/**
	 * @var Container
	 */
	protected $c;

	function __construct(Application $app)
	{
		$this->c = Container::getInstance($app);
	}

	function createForm(array $parameters = [])
	{
		return $this->c->getFormService()->createForm($parameters);
	}

	protected function jsonError($message = '', array $data = array())
	{
		$data['error'] = true;
		$data['errorMessage'] = $message;

		return $this->json($data);
	}

	protected function jsonSuccess(array $data = array())
	{
		$data['error'] = false;

		return $this->json($data);
	}

	protected function json(array $data = array())
	{
		return new JsonResponse($data);
	}

} 