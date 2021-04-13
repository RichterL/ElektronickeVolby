<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Election\Election;
use Models\Entities\User;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IElectionMapper
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

	public function getDataSource(): IDataSource;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Election $election): bool;

	public function delete(Election $election): bool;
}
