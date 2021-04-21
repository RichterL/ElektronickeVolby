<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Entities\Election\AnswerCollection;
use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Election\Answer;
use App\Models\Entities\Election\Question;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface AnswerMapper
{
	public function create(array $data = []): Answer;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Answer;

	/** @return Answer[] */
	public function findAll(): AnswerCollection;

	/** @return Answer[] */
	public function findRelated(Question $question): AnswerCollection;

	public function deleteRelated(Question $question): bool;

	public function getDataSource(array $filter = []): IDataSource;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Answer $answer): bool;

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(Answer $answer): bool;
}
