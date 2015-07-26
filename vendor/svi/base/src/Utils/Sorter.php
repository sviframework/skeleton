<?php

namespace Svi\Base\Utils;

use Symfony\Component\HttpFoundation\Request;

class Sorter
{
	private $request;
	private $order;
	private $by;
	private $columns;

	public function __construct(array $columns = array(), Request $request, $defaultBy = 'id', $defaultOrder = 'desc')
	{
		$this->columns = array_merge(array('id'), $columns);

		$this->order = $request->query->has('order') ? $request->query->get('order') : $defaultOrder;
		if ($this->order != 'asc' && $this->order != 'desc') {
			$this->order = $defaultOrder;
		}
		$this->by = $request->query->has('order_by') ? $request->query->get('order_by') : $defaultBy;
		if (!in_array($this->by, $this->columns)) {
			$this->by = $defaultBy;
		}

		$this->request = $request;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function getBy()
	{
		return $this->by;
	}

	public function processColumns(array &$columns)
	{
		foreach ($columns as $key => &$col) {
			if ($key == $this->getBy()) {
				$col['ordered'] = true;
				$col['asc'] = $this->getOrder() == 'asc';
			}
			if (in_array($key, $this->columns)) {
				$col['ordering'] = true;
				$col['ordHref'] = $this->getQueryUrl($key);
			}
		}
	}

	public function getQueryUrl($by)
	{
		if ($this->request->getQueryString()) {
			$pairs = explode('&', $this->request->getQueryString());
		} else {
			$pairs = array();
		}
		$arguments = array();
		foreach ($pairs as $pair) {
			$arg = explode('=', $pair);
			$arguments[$arg[0]] = @$arg[1];
		}
		$arguments['order_by'] = $by;
		$arguments['order'] = $by == $this->by ? $this->order == 'desc' ? 'asc' : 'desc' : $this->order;
		$pairs = array();
		foreach ($arguments as $key => $arg) {
			$pairs[] = $key . '=' . $arg;
		}

		return '?' . implode('&', $pairs);
	}

}
