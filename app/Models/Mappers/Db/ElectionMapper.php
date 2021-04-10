<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use dibi;
use Exception;
use Models\Entities\Election\Election;
use Models\Entities\User;
use Models\Mappers\IElectionMapper;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

class ElectionMapper extends BaseMapper implements IElectionMapper
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

	protected $table = Tables::ELECTION;
	protected $voterTable = Tables::VOTER;

	private UserMapper $userMapper;
	private QuestionMapper $questionMapper;

	public function __construct(UserMapper $userMapper, QuestionMapper $questionMapper)
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
			if ($property == 'createdBy') {
				$election->$property = $this->userMapper->findOne(['id' => $data[$key]]);
				continue;
			}
			$election->$property = $data[$key];
		}
		$election->setQuestions($this->questionMapper->findRelated($election));

		return $election;
	}

	public function save(Election $election): bool
	{
		$data = [];
		foreach (self::MAP as $property => $key) {
			if (isset($election->$property)) {
				$propertyValue = $election->$property;
				if ($propertyValue instanceof User) {
					$propertyValue = $propertyValue->getId();
				}
				$data[$key] = $propertyValue;
			}
		}
		unset($data['id']);
		$id = $election->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
			if (!$id) {
				throw new Exception('insert failed');
			}
			$election->setId($id);
			return true;
		}

		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
		return true;
	}

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

	/** parent concrete implementetions */
	public function findOne(array $filter = []): ?Election
	{
		return parent::findOne($filter);
	}

	/** @return Election[] */
	public function findAll(): array
	{
		return parent::findAll();
	}
}
