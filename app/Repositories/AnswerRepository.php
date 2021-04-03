<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Election\Answer;
use Models\Entities\Election\Question;
use Models\Mappers\IAnswerMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class AnswerRepository
{
	private IAnswerMapper $answerMapper;

	public function __construct(IAnswerMapper $answerMapper)
	{
		$this->answerMapper = $answerMapper;
	}

	public function findById(int $id): ?Answer
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

	public function getDataSource(): IDataSource
	{
		return $this->answerMapper->getDataSource();
	}

	public function save(Answer $election)
	{
		return $this->answerMapper->save($election);
	}

	public function delete(Answer $election)
	{
		return $this->answerMapper->delete($election);
	}
}
