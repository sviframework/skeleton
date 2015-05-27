<?php

namespace Sv\CrudBundle\Controller;

use Doctrine\DBAL\Query\QueryBuilder;
use Sv\BaseBundle\Controller\Controller;
use Sv\BaseBundle\Forms\Form;
use Sv\CrudBundle\Entity\NestedSortableInterface;
use Sv\CrudBundle\Entity\SortableInterface;
use Sv\CrudBundle\Entity\RemovableInterface;
use Sv\BaseBundle\Utils\Paginator;
use Sv\BaseBundle\Utils\Sorter;
use Svi\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class CrudController extends Controller
{
	private $entity;

	public function indexAction()
	{
		if ($this->isSortable()) {
			return $this->getSortableList();
		}

		$routes = $this->getRoutes();

		$templateTable = [
			'columns' => [],
			'rows' => [],
			'delete' => @$routes['delete'],
			'edit' => @$routes['edit'],
		];

		$sortableColumns = [];
		foreach ($this->getListColumns() as $key => $c) {
			if (is_string($c) || (@$c['type'] != 'actions' && @$c['sortable'] !== false)) {
				$sortableColumns[] = $key;
			}
			$templateTable['columns'][$key] = array(
				'title' => is_string($c) ? $c : @$c['title'],
			);
		}

		$sorter = new Sorter($sortableColumns, $this->getRequest());
		$sorter->processColumns($templateTable['columns']);

		$db = $this->createQB()->from($this->getEntity()->getTableName(), '');

		$filter = $this->createForm(['method' => 'get']);
		$filter->setMethod('get');
		$filter->setId('filter');
		$this->buildFilter($filter);
		if (count($filter->getFields()) == 0) {
			$filter = null;
		}

		if ($filter) {
			foreach ($filter->getFields() as $f) {
				$f->setRequired(false);
			}
			$filter->handleRequest($this->getRequest());
			$this->applyFilter($filter->getData(), $db);
		}

		$className = $this->getClassName();
		if (new $className instanceof RemovableInterface) {
			$db->andWhere('removed <> 1');
		}
		$this->modifyQuery($db);
		$paginator = new Paginator($db->select('COUNT(*)')->execute()->fetchColumn(0), $this->getItemsPerPage(), $this->getRequest());

		$db->select('*');
		$db
			->setFirstResult($paginator->getCurrentPage() * $paginator->getItemsPerPage())
			->setMaxResults($paginator->getItemsPerPage())
			->orderBy($this->getEntity()->getDbColumnNames()[$sorter->getBy()], $sorter->getOrder());

		$templateTable['rows'] = $this->getTableRows($this->getEntity()->fetch($db));

		return $this->render($this->getIndexTemplate(), $this->getTemplateParameters(array(
			'data' => array(
				'filter' => $filter,
				'add' => @$routes['add'],
				'pages' => $paginator ? $paginator->getView() : false,
				'sorter' => $sorter,
				'table' => $templateTable,
				'title' => $this->getIndexTitle() ? $this->getIndexTitle() : 'crud.title.list' . str_replace('\\', '', $this->getClassName()),
				'tableClass' => str_replace('\\', '', $this->getClassName()),
			),
		)));
	}

	public function addAction()
	{
		return $this->getEditView();
	}

	public function editAction($id)
	{
		if (!($entity = $this->getEntity()->findOneBy([$this->getEntityIdFieldName() => $id]))) {
			throw new NotFoundHttpException();
		}

		return $this->getEditView($entity);
	}

	function deleteAction($id)
	{
		if (!($entity = $this->getEntity()->findOneBy([$this->getEntityIdFieldName() => $id]))) {
			throw new NotFoundHttpException();
		}
		$request = $this->getRequest();

		if ($entity instanceof RemovableInterface) {
			if ($entity->getRemoved()) {
				throw new NotFoundHttpException();
			}
		}

		$formDelete = $this->createForm()
			->add('delete', 'hidden', array(
				'data' => 'delete',
			))
			->add('submit', 'submit', array(
				'label' => 'crud.delete',
				'cancel' => $request->query->has('back') ? $request->query->get('back') : false,
				'template' => 'Sv/CrudBundle/Views/deleteSubmit',
			));

		if ($formDelete->handleRequest($request)->isValid()) {
			if ($formDelete->get('delete')->getData() == 'delete') {
				$this->delete($entity);
				$this->c->getAlertsManager()->addAlert('success', $this->c->getApp()->getTranslation()->trans('crud.success.delete'));

				return $this->crudRedirect();
			}
		}

		return $this->render($this->getDeleteTemplate(), $this->getTemplateParameters(array(
			'className' => str_replace('\\', '', $this->getClassName()),
			'entity' => $entity,
			'formDelete' => $formDelete,
			'baseTemplate' => $this->getBaseTemplate(),
		)));
	}

	protected function getEditView($entity = null)
	{
		if (!$entity) {
			$className = $this->getClassName();
			$entity = new $className();
			$add = true;
		} else {
			$add = false;
		}

		$form = $this->createForm();
		$this->buildForm($form, $entity);

		foreach ($form->getFields() as $key => $value) {
			$attr = $value->getAttr();
			if (@$attr['data-delete']) {
				$form->add('deletefile_' . $key, 'hidden');
			}
		}

		$form->add('submit', 'submit', array(
			'label' => $add ? 'crud.add' : 'crud.save',
			'cancel' => $this->getRequest()->query->has('back') ? $this->getRequest()->query->get('back') : false,
		));

		if ($form->handleRequest($this->getRequest())->isValid()) {
			$this->checkForm($form, $entity);
			if ($form->isValid()) {
				$this->save($entity, $form, array());

				$this->c->getAlertsManager()->addAlert('success', $add ? $this->c->getApp()->getTranslation()->trans('crud.success.add') :
					$this->c->getApp()->getTranslation()->trans('crud.success.edit'));

				return $this->crudRedirect();
			}
		}

		return $this->render($this->getEditTemplate(), $this->getTemplateParameters(array(
			'form' => $form,
			'add' => $add,
			'entity' => $entity,
		)));
	}

	protected function getSortableList()
	{
		$request = $this->getRequest();
		if ($request->isMethod('post')) {
			return $this->updateWeights();
		}

		$className = $this->getClassName();
		$instance = new $className;
		if (!($instance instanceof SortableInterface)) {
			throw new \Exception('Sortable CRUD requires what class implements SortableInterface');
		}

		$routes = $this->getRoutes();

		$db = $this->createQB()->from($this->getEntity()->getTableName(), '');

		$filter = $this->createForm(['method' => 'get']);
		$filter->setMethod('get');
		$this->buildFilter($filter);
		if (count($filter->getFields()) == 0) {
			$filter = null;
		}

		if ($filter) {
			foreach ($filter->getFields() as $f) {
				$f->setRequired(false);
			}
			$filter->handleRequest($this->getRequest());
			$this->applyFilter($filter->getData(), $db);
		}

		if ($instance instanceof RemovableInterface) {
			$db->andWhere('removed <> 1');
		}
		$this->modifyQuery($db);

		$items = array();
		foreach ($this->getEntity()->fetch($db->orderBy('weight', 'ASC')) as $i) {
			$item = array();
			foreach ($this->getListColumns() as $key => $value) {
				$col = $this->getColumnFieldValue($key, $value, $i);
				$col['colTitle'] = is_string($value) ? $value : @$value['title'];
				$item[$key] = $col;
			}
			if (!isset($item['id'])) {
				$item['id'] = array('type' => 'string', 'value' => $i->getFieldValue($this->getEntityIdFieldName()), 'hide' => true, 'notForPrint' => true);
			}
			if ($instance instanceof NestedSortableInterface) {
				$parent = null;
				$item['parent'] = $i->getParentId() ? $i->findOneBy([$i->getIdColumnName() => $i->getParentId()])->getFieldValue($this->getEntityIdFieldName()) : false;
				$item['children'] = array();
			}
			$items[$i->getFieldValue($this->getEntityIdFieldName())] = $item;
		}
		if ($instance instanceof NestedSortableInterface) {
			foreach ($items as $key => &$value) {
				if ($value['parent']) {
					$items[$value['parent']]['children'][$key] = &$value;
				}
			}
			unset($value);

			foreach ($items as $key => &$value) {
				if ($value['parent']) {
					unset($items[$key]);
				}
			}
		}

		return $this->render($this->getSortableTemplate(), $this->getTemplateParameters(array(
			'items' => $items,
			'routes' => array(
				'add' => @$routes['add'],
				'delete' => @$routes['delete'],
				'edit' => @$routes['edit'],
			),
			'nested' => $instance instanceof NestedSortableInterface,
			'filter' => $filter,
		)));
	}

	protected function updateWeights()
	{
		$data = $this->getRequest()->request->all();
		if (!isset($data['weights'])) {
			return $this->jsonError('Weights parameters is not specified');
		}
		foreach ($data['weights'] as $weight) {
			if ($i = $this->getEntity()->findOneBy([$this->getEntityIdFieldName() => $weight['id']])) {
				$i->setWeight($weight['weight']);
				if ($i instanceof NestedSortableInterface) {
					if ($weight['parent'] && $parent = $this->getEntity()->findOneBy([$this->getEntity()->getIdColumnName() => $weight['parent']])) {
						$i->setParentId($parent->getFieldValue($this->getEntityIdFieldName()));
					} else {
						$i->setParentId(NULL);
					}
				}
				$i->save();
			}
		}

		return $this->jsonSuccess(array('weights' => $data['weights']));
	}

	abstract protected function getBaseTemplate();

	abstract protected function getClassName();

	abstract protected function getListColumns();

	abstract protected function getRoutes();

	protected function getPaginatorMaxPages() {return 15;}

	protected function getItemsPerPage() {return 15;}

	protected function isSortable() {return false;}

	protected function buildForm(Form $form, $entity)
	{
		throw new \Exception('function buildForm not yet implemented in child class');
	}

	protected function buildFilter(Form $builder) {}

	protected function applyFilter(array $data, QueryBuilder $builder)
	{
		throw new \Exception('function applyFilter not yet implemented in child class');
	}

	protected function checkForm(Form $form, $entity) {}

	protected function save(Entity $entity, Form $form, array $exclude = array())
	{
		$data = $form->getData();

		foreach ($data as $key => $value) {
			if (!in_array($key, $exclude) && strpos($key, 'deletefile_') === false) {
				$attr = $form->get($key)->getAttr();

				if (array_key_exists('data-file', $attr)) {
					if ($value) {
						$uri = @$attr['data-uri'] ? $attr['data-uri'] : 'heap';
						$md5 = md5($value->getFilename());
						$uri .= '/' . substr($md5, 0, 2) . '/' . substr($md5, 2, 2);
					} else {
						$uri = null;
					}

					$entity->setFieldValue($key,
						$this->c->getFileManager()->getNewFileUriFromField($entity->getFieldValue($key), $value, $uri,
							@$data['deletefile_' . $key] ? true : false)
					);
				} else {
					$entity->setFieldValue($key, $value);
				}
			}
		}
		$entity->save();
	}

	protected function getFileFieldAttributes($fileUri, $dir = null, $canDelete = true, $isImage = true)
	{
		$attr = array();
		$attr['data-file'] = $fileUri ? '/files/' . $fileUri : null;
		if ($canDelete) {
			$attr['data-delete'] = true;
		}
		if ($fileUri && $isImage) {
			$attr['data-image'] = $this->c->getImageManager()->getImagePath($fileUri, 120, 80);
		}
		if ($dir) {
			$attr['data-uri'] = $dir;
		}

		return $attr;
	}

	protected function delete(Entity $entity)
	{
		if ($entity instanceof RemovableInterface) {
			$entity->remove();
			$entity->save();
		} else {
			$entity->delete();
		}
	}

	protected function modifyQuery(QueryBuilder $builder) {}

	protected function getTableRows(array $items)
	{
		$rows = array();

		foreach ($items as $i) {
			$row = array();
			foreach ($this->getListColumns() as $key => $c) {
				$row[$key] = $this->getColumnFieldValue($key, $c, $i);
			}
			if (!isset($row[$this->getEntityIdFieldName()])) {
				$row[$this->getEntityIdFieldName()] = array('type' => 'string', 'value' => $i->getFieldValue($this->getEntityIdFieldName()), 'hide' => true);
			}
			$rows[] = $row;
		}

		return $rows;
	}

	protected function getColumnFieldValue($key, $column, Entity $item)
	{
		$value = is_string($column) ? NULL : @$column['value'];
		if (!is_string($column) && @$column['type'] == 'actions') {
			$value = $column;
		} else if ($value === NULL) {
			$value = $item->getFieldValue($key);
		} else if (is_callable($value)) {
			$value = $value($item);
		}
		if (is_object($value)) {
			$value = $value . '';
		}
		if (!is_array($value)) {
			if (is_bool($value)) {
				$value = array('type' => 'boolean', 'value' => $value);
			} else {
				$value = array('type' => 'string', 'value' => $value);
			}
		} else if (@$value['type'] == 'actions') {
			$value['entity'] = $item;
		}

		return $value;
	}

	protected function getTemplateParameters(array $parameters = [])
	{
		return $parameters + [
			'baseTemplate' => $this->getBaseTemplate(),
			'className' => str_replace('\\', '', $this->getClassName()),
			'templates' => [
				'delete' => $this->getDeleteTemplate(),
				'edit' => $this->getEditTemplate(),
				'fields' => $this->getFieldsTemplate(),
				'filter' => $this->getFilterFieldsTemplate(),
				'index' => $this->getIndexTemplate(),
				'paginator' => $this->getPaginatorTemplate(),
				'sortable' => $this->getSortableTemplate(),
				'sortableItems' => $this->getSortableItemsTemplate(),
				'table' => $this->getTableTemplate(),
				'field' => $this->getFieldTemplate(),
			],
		];
	}

	protected function getDeleteTemplate() { return 'Sv/CrudBundle/Views/delete.twig'; }

	protected function getEditTemplate() { return 'Sv/CrudBundle/Views/edit.twig'; }

	protected function getFieldsTemplate() { return 'Sv/CrudBundle/Views/fields.twig'; }

	protected function getFilterFieldsTemplate() { return 'Sv/CrudBundle/Views/filter_fields.twig'; }

	protected function getIndexTemplate() { return 'Sv/CrudBundle/Views/index.twig'; }

	protected function getPaginatorTemplate() { return 'Sv/CrudBundle/Views/paginator.twig'; }

	protected function getSortableTemplate() { return 'Sv/CrudBundle/Views/sortable.twig'; }

	protected function getSortableItemsTemplate() { return 'Sv/CrudBundle/Views/subitems.twig'; }

	protected function getTableTemplate() { return 'Sv/CrudBundle/Views/table.twig'; }

	protected function getFieldTemplate() { return 'Sv/CrudBundle/Views/table_field.twig'; }

	/**
	 * @return Entity
	 */
	protected function getEntity()
	{
		if (!isset($this->entity)) {
			$className = $this->getClassName();
			$this->entity = new $className();
		}

		return $this->entity;
	}

	protected function getEntityIdFieldName()
	{
		return $this->getEntity()->getIdFieldName();
	}

	protected function crudRedirect($url = NULL)
	{
		return $this->redirectToUrl($this->getBackLink($url));
	}
	
	protected function getBackLink($url = NULL)
	{
		$request = $this->getRequest();
		if (!$url) {
			if ($request->query->has('back')) {
				$url = $request->query->get('back');
			} else if ($request->headers->get('referer')) {
				$url = $request->headers->get('referer');
			} else {
				$url = $request->getRequestUri();
			}
		}

		return $url;
	}

	protected function getIndexTitle(){return null;}

}
