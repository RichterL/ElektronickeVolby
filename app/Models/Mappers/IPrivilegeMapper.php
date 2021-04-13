<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Resource\Privilege;
use Models\Entities\Resource\PrivilegeCollection;
use Models\Entities\Resource\Resource;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IPrivilegeMapper
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

	public function getDataSource(): IDataSource;

	public function delete(Privilege $privilege): bool;
}
