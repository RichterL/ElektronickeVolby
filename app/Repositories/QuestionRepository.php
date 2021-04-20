<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\Question;
use App\Models\Entities\Election\QuestionCollection;
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

	public function findAll(): QuestionCollection
	{
		return $this->questionMapper->findAll();
	}

	public function findRelated(Election $election): QuestionCollection
	{
		return $this->questionMapper->findRelated($election);
	}

	/**
	 * @param array<string, mixed> $filter
	 */
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

	public function delete(Question $election): bool
	{
		return $this->questionMapper->delete($election);
	}
}
