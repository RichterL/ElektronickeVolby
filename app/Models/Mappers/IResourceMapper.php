<?php

namespace Models\Mappers;

use Models\Entities\Resource\Resource;

interface IResourceMapper
{
	public function create(array $data = []): Resource;

	public function findOne(array $filter = []): ?Resource;

	/** @return Resource[] */
	public function findAll(): array;

	public function save(Resource $resource): bool;
}
