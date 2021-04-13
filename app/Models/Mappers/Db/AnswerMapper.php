<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use dibi;
use Exception;
use App\Models\Entities\Election\Answer;
use App\Models\Entities\Election\Question;
use App\Models\Mappers\IAnswerMapper;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

class AnswerMapper extends BaseMapper implements IAnswerMapper
{
	protected const MAP = [
		'id' => 'id',
		'question' => 'question_id',
		'value' => 'value',
	];

	protected string $table = Tables::ANSWER;

	public function create(array $data = []): Answer
	{
		$answer = new Answer();
		if (!empty($data)) {
			$answer->setId($data['id']);
			$answer->setValue($data['value']);
		}
		return $answer;
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Answer $answer): bool
	{
		return $this->saveWithId($answer);
	}

	/** @return Answer[] */
	public function findRelated(Question $question): array
	{
		$result = $this->dibi->select('*')->from($this->table)->where('question_id = %i', $question->getId())->fetchAll();
		$answers = [];
		/** @var Row */
		foreach ($result as $row) {
			$answers[] = $this->create($row->toArray());
		}
		return $answers;
	}

	public function deleteRelated(Question $question): bool
	{
		return (bool) $this->dibi->delete($this->table)->where('question_id = %i', $question->getId())->execute(dibi::AFFECTED_ROWS);
	}

	public function getDataSource(array $filter = []): DibiFluentDataSource
	{
		$fluent = $this->dibi->select('a.*, q.question')
			->from('%n a', $this->table)
			->leftJoin('%n q', Tables::QUESTION)->on('q.id = a.question_id');
		if (!empty($filter)) {
			$fluent->where($filter);
		}
		return new DibiFluentDataSource($fluent, 'id');
	}


	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Answer
	{
		return parent::findOne($filter);
	}

	/** @return Answer[] */
	public function findAll(): array
	{
		return (array) parent::findAll();
	}
}
