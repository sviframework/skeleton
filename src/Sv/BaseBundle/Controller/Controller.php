<?php

namespace Sv\BaseBundle\Controller;

use Sv\BaseBundle\Container;
use Svi\Application;
use Symfony\Component\HttpFoundation\Response;

class Controller extends \Svi\Controller
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
		return $this->c->getFormManager()->createForm($parameters);
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
		return new Response(json_encode($data));
	}

} 