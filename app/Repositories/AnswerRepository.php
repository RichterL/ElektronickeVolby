<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Election\Answer;
use App\Models\Entities\Election\Question;
use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\AnswerMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class AnswerRepository
{
	private AnswerMapper $answerMapper;

	public function __construct(AnswerMapper $answerMapper)
	{
		$this->answerMapper = $answerMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id): Answer
	{
		return $this->answerMapper->findOne(['id' => $id]);
	}

	/** @return Answer[] */
	public function findAll(): array
	{
		return $this->answerMapper->findAll();
	}

	/** @return Answer[] */
	public function findRelated(Question $question): array
	{
		return $this->answerMapper->findRelated($question);
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->answerMapper->getDataSource($filter);
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Answer $answer): bool
	{
		return $this->answerMapper->save($answer);
	}

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(Answer $answer): bool
	{
		return $this->answerMapper->delete($answer);
	}

	public function deleteRelated(Question $question): bool
	{
		return $this->answerMapper->deleteRelated($question);
	}
}
