<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Resource\Privilege;
use Models\Entities\Resource\PrivilegeCollection;
use Models\Entities\Resource\Resource;

interface IPrivilegeMapper
{
	public function create(array $data = []): Privilege;

	public function save(Resource $resource, Privilege $privilege): bool;

	public function findRelated(Resource $resource): PrivilegeCollection;

	public function findOne(array $filter = []): ?Privilege;

	/** @return Privilege[] */
	public function findAll(): array;
}
