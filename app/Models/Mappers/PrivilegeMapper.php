<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Resource\Privilege;
use App\Models\Entities\Resource\PrivilegeCollection;
use App\Models\Entities\Resource\Resource;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface PrivilegeMapper
{
	public function create(array $data = []): Privilege;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Resource $resource, Privilege $privilege): bool;

	public function findRelated(Resource $resource): PrivilegeCollection;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): ?Privilege;

	public function findAll(): PrivilegeCollection;

	public function getDataSource(array $filter = []): IDataSource;

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(Privilege $privilege): bool;
}
