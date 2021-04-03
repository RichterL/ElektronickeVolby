<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Election\Answer;
use Models\Entities\Election\Question;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IAnswerMapper
{
	public function create(array $data = []): Answer;

	public function findOne(array $filter = []): ?Answer;

	/** @return Answer[] */
	public function findAll(): array;

	/** @return Answer[] */
	public function findRelated(Question $question): array;

	public function getDataSource(): IDataSource;

	public function save(Answer $election): bool;

	public function delete(Answer $election): bool;
}
