<?php

namespace Sv\CrudBundle\Entity;

interface NestedSortableInterface extends SortableInterface
{

	public function getParentId();

	public function setParentId($parentId = null);

} 