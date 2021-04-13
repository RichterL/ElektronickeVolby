<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Election\Answer;
use Models\Entities\Election\Question;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IAnswerMapper
{
	public function create(array $data = []): Answer;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): ?Answer;

	/** @return Answer[] */
	public function findAll(): array;

	/** @return Answer[] */
	public function findRelated(Question $question): array;

	public function deleteRelated(Question $question): bool;

	public function getDataSource(): IDataSource;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Answer $answer): bool;

	public function delete(Answer $answer): bool;
}
