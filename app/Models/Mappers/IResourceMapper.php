<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Resource\Resource;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IResourceMapper
{
	public function create(array $data = []): Resource;

	public function findOne(array $filter = []): ?Resource;

	/** @return Resource[] */
	public function findAll(): array;

	public function save(Resource $resource): bool;

	public function getDataSource(array $filter = []): IDataSource;

	public function delete(Resource $resource): bool;
}
