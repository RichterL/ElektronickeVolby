<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use dibi;
use Dibi\Row;
use ErrorException;
use Models\Entities\Election\Election;
use Models\Entities\Election\VoterFile;
use Models\Entities\IdentifiedById;
use Models\Mappers\IVoterFileMapper;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

class VoterFileMapper extends BaseMapper implements IVoterFileMapper
{
	protected const MAP = [
		'id' => 'id',
		'election' => 'election_id',
		'filename' => 'filename',
		'content' => 'content',
		'createdAt' => 'created_at',
		'createdBy' => 'created_by',
	];

	protected const DATA_TYPES = [
		'content' => \Dibi\Type::BINARY,
	];

	protected $table = Tables::VOTER_FILE;
	private ElectionMapper $electionMapper;
	private UserMapper $userMapper;

	public function __construct(ElectionMapper $electionMapper, UserMapper $userMapper)
	{
		$this->userMapper = $userMapper;
		$this->electionMapper = $electionMapper;
	}

	public function save(VoterFile $voterFile): bool
	{
		$data = [];
		foreach (self::MAP as $property => $key) {
			if (isset($voterFile->$property)) {
				if ($voterFile->$property instanceof IdentifiedById) {
					$data[$key] = $voterFile->$property->getId();
					continue;
				}
				if ($property == 'content') {
					$data[$key] = $voterFile->getContent(true);
					continue;
				}
				$data[$key] = $voterFile->$property;
			}
		}
		unset($data['id']);
		$id = $voterFile->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
			if (!$id) {
				throw new ErrorException('insert failed');
			}
			$voterFile->setId($id);
			return true;
		}
		return (bool) $this->dibi->update($this->table, $data)->where('id = %i', $id)->execute(dibi::AFFECTED_ROWS);
	}

	public function findRelated(Election $election): array
	{
		$voterFiles = [];
		$result = $this->dibi->select('id, election_id, filename, created_at, created_by')->from($this->table)->where('election_id = %i', $election->getId())->fetchAll();
		/** @var Row */
		foreach ($result as $row) {
			$voterFiles[] = $this->create($row->toArray());
		}
		return $voterFiles;
	}

	public function create(array $data = []): VoterFile
	{
		$voterFile = new VoterFile();
		if (!empty($data)) {
			$voterFile->setId($data['id']);
			$voterFile->setElection($this->electionMapper->findOne(['id' => $data['election_id']]));
			if (!empty($data['content'])) {
				$voterFile->setContent($data['content'], true);
			}
			$voterFile->filename = $data['filename'];
			$voterFile->createdAt = $data['created_at'];
			$voterFile->createdBy = $this->userMapper->findOne(['id' => $data['created_by']]);
		}
		return $voterFile;
	}

	public function getDataSource(array $filter = []): DibiFluentDataSource
	{
		$fluent = $this->dibi->select('id, election_id, filename, created_at, created_by')->from($this->table);
		if (!empty($filter)) {
			$fluent->where($filter);
		}
		return new DibiFluentDataSource($fluent, 'id');
	}

	public function findOne(array $filter = []): ?VoterFile
	{
		return parent::findOne($filter);
	}

	/** @return VoterFile[] */
	public function findAll(): array
	{
		return (array) parent::findAll();
	}
}
