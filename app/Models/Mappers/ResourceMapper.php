<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Resource\Resource;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface ResourceMapper
{
	public function create(array $data = []): \App\Models\Entities\Resource\Resource;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): \App\Models\Entities\Resource\Resource;

	/** @return Resource[] */
	public function findAll(): array;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Resource $resource): bool;

	public function getDataSource(array $filter = []): IDataSource;

	public function delete(Resource $resource): bool;
}
