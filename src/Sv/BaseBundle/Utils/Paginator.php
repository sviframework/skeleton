<?php

namespace Sv\BaseBundle\Utils;

use Symfony\Component\HttpFoundation\Request;

class Paginator
{
	private $totalItems;
	private $itemsPerPage;
	private $currentPage;
	private $request;
	private $maxPages;

	public function __construct($totalItems, $itemsPerPage, Request $request, $maxPages = 15)
	{
		$this->maxPages = $maxPages;
		$this->totalItems = $totalItems;
		$this->itemsPerPage = $itemsPerPage;
		$this->currentPage = $request->query->has('page') ? $request->query->get('page') : 0;
		$this->request = $request;
	}

	public function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage;
	}

	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	public function setItemsPerPage($itemsPerPage)
	{
		$this->itemsPerPage = $itemsPerPage;
	}

	public function getItemsPerPage()
	{
		return $this->itemsPerPage;
	}

	public function setTotalItems($totalItems)
	{
		$this->totalItems = $totalItems;
	}

	public function getTotalItems()
	{
		return $this->totalItems;
	}

	public function setRequest($request)
	{
		$this->request = $request;
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function getView()
	{
		$pages = array();
		$pageCount = $this->itemsPerPage ? ceil($this->totalItems / $this->itemsPerPage) : 1;

		if ($pageCount > 1) {
			$half = round(($this->maxPages - 1) / 2);
			$first = $this->currentPage - $half;
			if ($first < 0) {
				$first = 0;
			}
			$last = $first + $this->maxPages;
			if ($last > $pageCount) {
				$difference = $last - $pageCount;
				$last = $pageCount;
				$first = $first - $difference;
				if ($first < 0) {
					$first = 0;
				}
			}
			if ($this->maxPages < $pageCount) {
				$pages[] = array(
					'page' => '<<',
					'href' => $this->getQueryUrl(0),
					'current' => $this->currentPage == 0,
				);
			}
			for ($n = $first; $n < $last; $n++) {
				$pages[] = array(
					'page' => $n + 1,
					'href' => $this->getQueryUrl($n),
					'current' => $n == $this->currentPage,
				);
			}
			if ($this->maxPages < $pageCount) {
				$pages[] = array(
					'page' => '>>',
					'href' => $this->getQueryUrl($pageCount - 1),
					'current' => $this->currentPage == $pageCount - 1,
				);
			}

			$pages[0]['class'] = 'first';
			$pages[count($pages) - 1]['class'] = 'last';
		}

		return $pages;
	}

	protected function getQueryUrl($page)
	{
		if ($this->request->getQueryString()) {
			$pairs = explode('&', $this->request->getQueryString());
		} else {
			$pairs = array();
		}
		$arguments = array();
		foreach ($pairs as $pair) {
			$arg = explode('=', $pair);
			$arguments[$arg[0]] = $arg[1];
		}
		$arguments['page'] = $page;
		$pairs = array();
		foreach ($arguments as $key => $arg) {
			$pairs[] = $key . '=' . $arg;
		}

		return '?' . implode('&', $pairs);
	}

}
