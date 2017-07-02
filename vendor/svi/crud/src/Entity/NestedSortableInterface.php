<?php

namespace Svi\Crud\Entity;

interface NestedSortableInterface extends SortableInterface
{

	public function getParentId();

	public function setParentId($parentId = null);

	public function getWeight();

	public function setWeight($weight);

} 