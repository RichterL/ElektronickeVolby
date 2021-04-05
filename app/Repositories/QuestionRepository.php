<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Election\Election;
use Models\Entities\Election\Question;
use Models\Mappers\IQuestionMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class QuestionRepository
{
	private IQuestionMapper $questionMapper;

	public function __construct(IQuestionMapper $questionMapper)
	{
		$this->questionMapper = $questionMapper;
	}

	public function findById(int $id): ?Question
	{
		return $this->questionMapper->findOne(['id' => $id]);
	}

	/** @return Question[] */
	public function findAll(): array
	{
		return $this->questionMapper->findAll();
	}

	/** @return Question[] */
	public function findRelated(Election $election): array
	{
		return $this->questionMapper->findRelated($election);
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->questionMapper->getDataSource($filter);
	}

	public function save(Question $election): bool
	{
		return $this->questionMapper->save($election);
	}

	public function saveData(Question $election): bool
	{
		return $this->questionMapper->saveData($election);
	}

	public function delete(Question $election): bool
	{
		return $this->questionMapper->delete($election);
	}
}
