<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\Question;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\QuestionMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class QuestionRepository
{
	private QuestionMapper $questionMapper;

	public function __construct(QuestionMapper $questionMapper)
	{
		$this->questionMapper = $questionMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id): Question
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

	/**
	 * @throws SavingErrorException
	 */
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
