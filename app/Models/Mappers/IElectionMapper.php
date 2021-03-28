<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Election\Election;

interface IElectionMapper
{
	public function create(array $data = []): Election;

	public function findOne(array $filter = []): ?Election;

	public function findAll(): array;

	public function getDataSource();

	public function save(Election $election): bool;

	public function delete(Election $election): bool;
}
