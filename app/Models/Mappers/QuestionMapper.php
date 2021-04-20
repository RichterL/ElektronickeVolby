<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Entities\Election\QuestionCollection;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\Question;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface QuestionMapper
{
	public function create(array $data = []): Question;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Question;

	public function findAll(): QuestionCollection;

	public function findRelated(Election $election): QuestionCollection;

	public function getDataSource(array $filter = []): IDataSource;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Question $question): bool;

	public function delete(Question $question): bool;
}
