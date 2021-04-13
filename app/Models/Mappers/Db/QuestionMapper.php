<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use dibi;
use Dibi\DriverException;
use Dibi\Row;
use Exception;
use Models\Entities\Election\Election;
use Models\Entities\Election\Question;
use Models\Mappers\IQuestionMapper;

class QuestionMapper extends BaseMapper implements IQuestionMapper
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

	private AnswerMapper $answerMapper;

	public function __construct(AnswerMapper $answerMapper)
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

	public function findRelated(Election $election): array
	{
		$result = $this->dibi->select('*')->from($this->table)->where('election_id = %i', $election->getId())->execute();
		self::applyDataTypes($result);
		$result->fetchAll();
		$questions = [];
		/** @var Row $row */
		foreach ($result as $row) {
			$questions[] = $this->create($row->toArray());
		}
		return $questions;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Question
	{
		return parent::findOne($filter);
	}

	/** @return Question[] */
	public function findAll(): array
	{
		return (array) parent::findAll();
	}
}
