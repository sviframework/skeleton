<?php

namespace Svi\BaseBundle\Forms;

use Svi\BaseBundle\Container;
use Symfony\Component\HttpFoundation\Request;

class Form
{
	private $id = 'form';
	/**
	 * @var Container
	 */
	private $c;
	private $action = '';
	/** @var Field[] $fields */
	private $fields = array();
	private $parameters;
	private $method = 'post';
	private $submitted = false;
	private $errors = array();
	private $csrf = true;

	public function __construct(Container $c, array $parameters = [])
	{
		$this->c = $c;
		$this->parameters = $parameters;
		if (isset($parameters['method']) && strtolower($parameters['method']) == 'get') {
			$this->method = 'get';
		}
		if (isset($parameters['csrf'])) {
			$this->csrf = $parameters['csrf'];
		}
	}

	public function handleRequest(Request $request)
	{
		$data = $this->method == 'post' ? $request->request->all() : $request->query->all();
		$data = array_merge($data, $_FILES);

		if (!isset($data['form_id']) || !$data['form_id'] || $data['form_id'] != $this->getId()) {
			$this->submitted = false;
			return $this;
		}

		$this->submitted = true;
		foreach ($this->fields as $key => $f) {
			if (!$f->isReadOnly()) {
				$f->setData(isset($data[$key]) ? $data[$key] : null);
				$f->validateData();
			}
		}

		if ($this->submitted) {
			if ($this->getCsrf()) {
				$referer = $request->headers->get('referer');

				if (strtolower($request->getHost()) != strtolower(parse_url($referer, PHP_URL_HOST))) {
					$this->addError('Неправильные параметры формы (csrf)');
				}
			}
		}

		return $this;
	}

	public function isSubmitted()
	{
		return $this->submitted;
	}

	public function isValid()
	{
		if (!$this->isSubmitted()) {
			return false;
		}
		if ($this->hasErrors()) {
			return false;
		}
		foreach ($this->fields as $f) {
			if ($f->hasErrors()) {
				return false;
			}
		}

		return true;
	}

    /**
     * @return Field[]
     */
	public function getFields()
	{
		return $this->fields;
	}

	public function add($name, $type, array $parameters = array())
	{
		if (is_string($name)) {
			$className = __NAMESPACE__ . '\\' .
                str_replace('_', '', ucwords(strtolower($type), '_')) . 'Field';
			$this->addField(new $className($name, $parameters));
		} else {
			$this->addField($name);
		}

		return $this;
	}

	/**
	 * @param $name
	 * @return Field
	 */
	public function get($name)
	{
		return $this->fields[$name];
	}

	public function has($name)
	{
		return array_key_exists($name, $this->fields);
	}

	public function getData()
	{
		$result = array();
		foreach ($this->fields as $key => $f) {
			if (!$f->isNotInput()) {
				$result[$key] = $f->getData();
			}
		}

		return $result;
	}

	public function renderStart()
	{
		return $this->renderView('formBegin', [
			'noValidate' => $this->getNoValidate(),
			'method' => $this->getMethod(),
			'errors' => $this->getErrors(),
			'enctype' => $this->getEncType(),
			'attr' => $this->getAttr(),
			'action' => $this->getAction(),
			'id' => $this->getId(),
		]);
	}

	public function renderRestFields()
	{
		$result = '';

		foreach ($this->fields as $f) {
			if (!$f->getRendered()) {
				$result .= $this->renderFieldView($f);
			}
		}

		return $result;
	}

	public function renderEnd()
	{
		return $this->renderRestFields() . $this->renderView('formEnd');
	}

	public function renderField($name)
	{
		if (!($field = $this->fields[$name])) {
			throw new \Exception('There is no field with name "' . $name . '"');
		}

		return $this->renderFieldView($field);
	}

	public function render()
	{
		$result = $this->renderStart();
		foreach ($this->fields as $f) {
			$result .= $this->renderFieldView($f);
		}
		return $result . $this->renderEnd();
	}

	/**
	 * @param string $method
	 * @return Form
	 */
	public function setMethod($method)
	{
		$this->method = strtolower($method);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	public function getEncType()
	{
		foreach ($this->fields as $f) {
			if ($f instanceof FileField) {
				return 'multipart/form-data';
			}
		}

		return null;
	}

	/**
	 * @param mixed $templatePath
	 * @return Form
	 */
	public function setTemplatesPath($templatePath)
	{
		$this->parameters['templatesPath'] = $templatePath;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTemplatesPath()
	{
		return isset($this->parameters['templatesPath']) ? $this->parameters['templatesPath'] : null;
	}

	public function getNoValidate()
	{
		return isset($this->parameters['noValidate']) ? $this->parameters['noValidate'] : null;
	}

	public function setNoValidate($value)
	{
		$this->parameters['noValidate'] = $value;
		return $this;
	}

	public function addError($error)
	{
		$this->errors[] = $error;

		return $this;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function hasErrors()
	{
		return count($this->errors) > 0;
	}

	public function addField(Field $field)
	{
		if (array_key_exists($field->getName(), $this->fields)) {
			throw new \Exception('Field with name "' . $field->getName() . '" already exist');
		}
		$this->fields[$field->getName()] = $field;

		return $this;
	}

	public function remove($fieldKey)
	{
		if (array_key_exists($fieldKey, $this->fields)) {
			unset($this->fields[$fieldKey]);
		}
	}

	public function setAttr(array $value)
	{
		$this->parameters['attr'] = $value;

		return $this;
	}

	public function getAttr()
	{
		return !isset($this->parameters['attr']) || $this->parameters['attr'] === null ? [] : $this->parameters['attr'];
	}

	protected function renderFieldView(Field $field)
	{
		$field->setRendered(true);
		return $this->renderView($field->getTemplate() ? $field->getTemplate() : 'field', $field->getViewParameters());
	}

	protected function renderView($template, array $params = [])
	{
		if (strpos($template, '/') !== false) {
			$templatePath = $template;
		} else {
			$templatePath = ($this->getTemplatesPath() ? $this->getTemplatesPath() : 'svi/base/src/Forms/Views') . '/' .$template;
		}
		return $this->c->getApp()->getTemplateService()->render($templatePath, $params);
	}

	/**
	 * @param string $action
	 * @return Form
	 */
	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @param boolean $csrf
	 * @return Form
	 */
	public function setCsrf($csrf)
	{
		$this->csrf = $csrf;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCsrf()
	{
		return $this->csrf;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

} 