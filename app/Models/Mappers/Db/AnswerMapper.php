<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use dibi;
use Exception;
use Models\Entities\Election\Answer;
use Models\Entities\Election\Question;
use Models\Mappers\IAnswerMapper;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class AnswerMapper extends BaseMapper implements IAnswerMapper
{
	protected const MAP = [
		'id' => 'id',
		'value' => 'value',
	];

	protected $table = Tables::ANSWER;

	public function create(array $data = []): Answer
	{
		$answer = new Answer();
		if (!empty($data)) {
			$answer->setId($data['id']);
			$answer->setValue($data['value']);
		}
		return $answer;
	}

	public function save(Answer $answer): bool
	{
		$data = [];
		foreach (self::MAP as $property => $key) {
			if (isset($answer->$property)) {
				$propertyValue = $answer->$property;
				$data[$key] = $propertyValue;
			}
		}
		unset($data['id']);
		$id = $answer->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
			if (!$id) {
				throw new Exception('insert failed');
			}
			$answer->setId($id);
			return true;
		}

		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
		return true;
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

	/** parent concrete implementetions */
	public function findOne(array $filter = []): ?Answer
	{
		return parent::findOne($filter);
	}

	/** @return Answer[] */
	public function findAll(): array
	{
		return (array) parent::findAll();
	}
}
