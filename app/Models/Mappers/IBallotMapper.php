<?php

namespace App\Models\Mappers;

use App\Models\Entities\Election\Ballot;
use Models\Entities\IdentifiedById;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

interface IBallotMapper
{
	public function findOne(array $filter = []): Ballot;

	/**
	 * @return Ballot[]
	 */
	public function find(array $filter = []): iterable;

	public function delete(IdentifiedById $entity): bool;

	public function getDataSource(array $filter = []): DibiFluentDataSource;
}