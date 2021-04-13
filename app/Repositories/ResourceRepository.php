<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Resource\Resource;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\IResourceMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class ResourceRepository extends BaseRepository
{
	private IResourceMapper $resourceMapper;

	public function __construct(IResourceMapper $resourceMapper)
	{
		$this->resourceMapper = $resourceMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id): Resource
	{
		return $this->resourceMapper->findOne(['id' => $id]);
	}

	public function findByUsername(string $username): Resource
	{
		return $this->resourceMapper->findOne(['username' => $username]);
	}

	/** @return Resource[] */
	public function findAll(): array
	{
		return $this->cache->load('resource.findAll', function () {
			return $this->resourceMapper->findAll();
		});
	}

	public function getIdNamePairs()
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
	public function save(Resource $resource): bool
	{
		return $this->resourceMapper->save($resource);
	}

	public function getDataSource(): IDataSource
	{
		return $this->resourceMapper->getDataSource();
	}

	public function delete(Resource $resource): bool
	{
		return $this->resourceMapper->delete($resource);
	}
}
