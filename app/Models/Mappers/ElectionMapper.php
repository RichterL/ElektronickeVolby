<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Election\Election;
use App\Models\Entities\User;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface ElectionMapper
{
	public function create(array $data = []): Election;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Election;

	/** @return Election[] */
	public function find(array $filter = []): iterable;

	public function findAll(): array;

	/** @return Election[] */
	public function findRelated(User $user): iterable;

	public function getDataSource(array $filter = []): IDataSource;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Election $election): bool;

	public function delete(Election $election): bool;
}
