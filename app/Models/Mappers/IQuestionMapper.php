<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Election\Election;
use Models\Entities\Election\Question;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IQuestionMapper
{
	public function create(array $data = []): Question;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): ?Question;

	/** @return Question[] */
	public function findAll(): array;

	/** @return Question[] */
	public function findRelated(Election $election): array;

	public function getDataSource(): IDataSource;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Question $question): bool;

	public function delete(Question $question): bool;
}
