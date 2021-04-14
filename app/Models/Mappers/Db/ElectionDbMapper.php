<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Election\Election;
use App\Models\Entities\User;
use App\Models\Mappers\ElectionMapper;

class ElectionDbMapper extends BaseDbMapper implements ElectionMapper
{
	protected const MAP = [
		'id' => 'id',
		'title' => 'title',
		'description' => 'description',
		'active' => 'active',
		'secret' => 'secret',
		'start' => 'start',
		'end' => 'end',
		'createdAt' => 'created_at',
		'createdBy' => 'created_by',
	];

	protected const DATA_TYPES = [
		'active' => \Dibi\Type::BOOL,
		'secret' => \Dibi\Type::BOOL,
	];

	protected string $table = Tables::ELECTION;
	protected string $voterTable = Tables::VOTER;

	private UserDbMapper $userMapper;
	private QuestionDbMapper $questionMapper;

	public function __construct(UserDbMapper $userMapper, QuestionDbMapper $questionMapper)
	{
		$this->userMapper = $userMapper;
		$this->questionMapper = $questionMapper;
	}

	public function create(array $data = []): Election
	{
		$election = new Election();
		if (empty($data)) {
			return $election;
		}
		foreach (self::MAP as $property => $key) {
			if ($property === 'createdBy') {
				$election->$property = $this->userMapper->findOne(['id' => $data[$key]]);
				continue;
			}
			$election->$property = $data[$key];
		}
		$election->setQuestions($this->questionMapper->findRelated($election));

		return $election;
	}

//	public function save(Election $election): bool
//	{
//		$data = [];
//		foreach (self::MAP as $property => $key) {
//			if (isset($election->$property)) {
//				$propertyValue = $election->$property;
//				if ($propertyValue instanceof User) {
//					$propertyValue = $propertyValue->getId();
//				}
//				$data[$key] = $propertyValue;
//			}
//		}
//		unset($data['id']);
//		$id = $election->getId();
//		if ($id === null) {
//			$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
//			if (!$id) {
//				throw new SavingErrorException('insert failed');
//			}
//			$election->setId($id);
//			return true;
//		}
//
//		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
//		return true;
//	}

	/**
	 * @return Election[]
	 */
	public function findRelated(User $user): iterable
	{
		$result = $this->dibi->select('*')
			->from('%n e', $this->table)
			->leftJoin('%n v', $this->voterTable)->on('v.election_id = e.id')
			->where('v.email = %s', $user->getEmail())
			->execute();
		self::applyDataTypes($result);

		$collection = self::createCollection();
		foreach ($result->fetchAll() as $row) {
			$collection[] = $this->create((array) $row);
		}
		return $collection;
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Election $election): bool
	{
		return $this->saveWithId($election);
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Election
	{
		return parent::findOne($filter);
	}

	/**
	 * @return Election[]
	 */
	public function findAll(): array
	{
		return parent::findAll();
	}
}
