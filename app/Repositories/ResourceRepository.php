<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Resource\Resource;
use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\ResourceMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class ResourceRepository extends BaseRepository
{
	public const CACHE_NAMESPACE = 'resources';

	private ResourceMapper $resourceMapper;

	public function __construct(ResourceMapper $resourceMapper)
	{
		$this->resourceMapper = $resourceMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id): \App\Models\Entities\Resource\Resource
	{
		return $this->resourceMapper->findOne(['id' => $id]);
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findByUsername(string $username): \App\Models\Entities\Resource\Resource
	{
		return $this->resourceMapper->findOne(['username' => $username]);
	}

	/**
	 * @return Resource[]
	 */
	public function findAll(): array
	{
		return $this->cache->load('findAll', function () {
			return $this->resourceMapper->findAll();
		});
	}

	/**
	 * @return string[]
	 */
	public function getIdNamePairs(): array
	{
		$resources = $this->resourceMapper->findAll();
		$pairs = [];
		foreach ($resources as $resource) {
			$pairs[$resource->id] = $resource->name;
		}
		return $pairs;
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Resource $resource): void
	{
		$this->resourceMapper->save($resource);
		$this->invalidate();
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->resourceMapper->getDataSource($filter);
	}

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(Resource $resource): bool
	{
		return $this->resourceMapper->delete($resource);
	}
}
