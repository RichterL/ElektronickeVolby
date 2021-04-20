<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

use App\Models\Entities\Election\QuestionCollection;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use Dibi\DriverException;
use Dibi\Row;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\Question;
use App\Models\Mappers\QuestionMapper;

class QuestionDbMapper extends BaseDbMapper implements QuestionMapper
{
	protected const MAP = [
		'id' => 'id',
		'name' => 'name',
		'question' => 'question',
		'election' => 'election_id',
		'required' => 'required',
		'min' => 'min',
		'max' => 'max',
	];

	protected const DATA_TYPES = [
		'required' => \Dibi\Type::BOOL,
		'multiple' => \Dibi\Type::BOOL,
	];

	protected string $table = Tables::QUESTION;

	private AnswerDbMapper $answerMapper;

	public function __construct(AnswerDbMapper $answerMapper)
	{
		$this->answerMapper = $answerMapper;
	}

	public function create(array $data = [], $includeAnswers = true): Question
	{
		$question = new Question();
		if (!empty($data)) {
			$question->setId($data['id']);
			$question->setName($data['name'])
				->setQuestion($data['question'])
				->setRequired($data['required'])
				->setMin($data['min'])
				->setMax($data['max']);
			$question->setAnswers($this->answerMapper->findRelated($question));
		}
		return $question;
	}

	public function save(Question $question): bool
	{
		try {
			$this->dibi->begin();
			$this->saveWithId($question);
			$this->answerMapper->deleteRelated($question);
			foreach ($question->getAnswers() as $answer) {
				$this->answerMapper->save($answer);
			}
			$this->dibi->commit();
			return true;
		} catch (DriverException $e) {
			$this->dibi->rollback();
			throw new SavingErrorException('Saving failed!');
		}
	}

//	public function saveData(Question $question): bool
//	{
//		$data = [];
//		foreach (self::MAP as $property => $key) {
//			if (isset($question->$property)) {
//				$propertyValue = $question->$property;
//				if ($propertyValue instanceof Election) {
//					$propertyValue = $propertyValue->getId();
//				}
//				$data[$key] = $propertyValue;
//			}
//		}
//		unset($data['id']);
//		$id = $question->getId();
//		if (empty($id)) {
//			$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
//			if (!$id) {
//				throw new Exception('insert failed');
//			}
//			$question->setId($id);
//			return true;
//		}
//
//		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
//		return true;
//	}

	public function findRelated(Election $election): QuestionCollection
	{
		$result = $this->dibi->select('*')->from($this->table)->where('election_id = %i', $election->getId())->execute();
		self::applyDataTypes($result);
		$result->fetchAll();
		$questions = self::createCollection();
		/** @var Row $row */
		foreach ($result as $row) {
			$questions[] = $this->create($row->toArray());
		}
		return $questions;
	}

	public static function createCollection(): QuestionCollection
	{
		return new QuestionCollection();
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Question
	{
		return parent::findOne($filter);
	}

	public function findAll(): QuestionCollection
	{
		return parent::findAll();
	}
}
