<?php

namespace App\Models\Mappers;

use App\Models\Entities\Election\Ballot;
use Models\Entities\IdentifiedById;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

interface IBallotMapper
{
	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Ballot;

	/**
	 * @return Ballot[]
	 */
	public function find(array $filter = []): iterable;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Ballot $ballot): bool;

	public function delete(Ballot $ballot): bool;

	public function getDataSource(array $filter = []): DibiFluentDataSource;
}