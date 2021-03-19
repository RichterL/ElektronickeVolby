<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Resource\Resource;
use Models\Mappers\IResourceMapper;

class ResourceRepository
{
	private IResourceMapper $resourceMapper;

	public function __construct(IResourceMapper $resourceMapper)
	{
		$this->resourceMapper = $resourceMapper;
	}

	public function findById(int $id): ?Resource
	{
		return $this->resourceMapper->findOne(['id' => $id]);
	}

	public function findByUsername(string $username): ?Resource
	{
		return $this->resourceMapper->findOne(['username' => $username]);
	}

	/** @return Resource[] */
	public function findAll(): array
	{
		return $this->resourceMapper->findAll();
	}

	public function save(Resource $resource): bool
	{
		return $this->resourceMapper->save($resource);
	}
}
