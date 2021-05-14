<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

use App\Models\Entities\Election\Ballot;
use App\Models\Entities\Election\Election;
use App\Models\Factories\BallotFactory;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\BallotMapper;

class BallotDbMapper extends BaseDbMapper implements BallotMapper
{
	protected const MAP = [
		'id' => 'id',
		'election' => 'election_id',
		'encryptedData' => 'encrypted_data',
		'decryptedData' => 'decrypted_data',
		'encryptedKey' => 'encrypted_key',
		'decryptedKey' => 'decrypted_key',
		'hash' => 'hash',
		'signature' => 'signature',
		'decryptedAt' => 'decrypted_at',
		'decryptedBy' => 'decrypted_by',
		'countedAt' => 'counted_at',
		'countedBy' => 'counted_by',
	];

	protected string $table = Tables::BALLOT;
	private BallotFactory $ballotFactory;

	public function __construct(BallotFactory $ballotFactory)
	{
		$this->ballotFactory = $ballotFactory;
	}

	public function create(array $data = []): Ballot
	{
		$tmp = [];
		foreach (self::MAP as $property => $key) {
			$tmp[$property] = $data[$key];
		}
		return $this->ballotFactory::create($tmp);
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Ballot $ballot): bool
	{
		$this->dibi->query('LOCK TABLES %n WRITE', $this->table);
		if ($ballot->getId() === null) {
			$electionId = $ballot->getElection()->getValue();
			$emptyRows = $this->getEmptyRows($electionId);
			$count = count($emptyRows);
			if ($count < 20) {

				for ($i = 0; $i < 20; $i++) {
					$emptyRows[] = $this->dibi->insert($this->table, [
						'election_id' => $electionId,
						'encrypted_data' => '',
						'encrypted_key' => '',
						'hash' => '',
						'signature' => '',
					])->execute(\dibi::IDENTIFIER);
					$count++;
				}
			}
			$randomIndex = random_int(0,$count-1);
			$ballot->setId($emptyRows[$randomIndex]);
		}
		$result = $this->saveWithId($ballot);
		$this->dibi->query('UNLOCK TABLES');
		return $result;
	}

	private function getEmptyRows(int $electionId): array
	{
		return (array) $this->dibi->select('id')
			->from($this->table)
			->where('election_id = %i', $electionId)
			->where('hash LIKE ""')
			->where('encrypted_data LIKE ""')
			->where('encrypted_key LIKE ""')
			->where('signature LIKE ""')
			->fetchAssoc('[]=id');
	}

	public function findEncrypted(Election $election): iterable
	{
		$collection = static::createCollection();
		$rows = $this->dibi->select('*')
			->from($this->table)
			->where(['election_id' => $election->getId(), 'decrypted_at' => null])
			->fetchAll();
		foreach ($rows as $row) {
			if (empty($row['encrypted_data'])) {
				continue;
			}
			$collection[] = $this->create($row->toArray());
		}
		return $collection;
	}

	public function findDecrypted(Election $election): iterable
	{
		$collection = static::createCollection();
		$rows = $this->dibi->select('*')
			->from($this->table)
			->where('election_id = %i', $election->getId())
			->where('decrypted_at IS NOT NULL')
			->fetchAll();
		foreach ($rows as $row) {
			$collection[] = $this->create($row->toArray());
		}
		return $collection;
	}


	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Ballot
	{
		return parent::findOne($filter);
	}

	/**
	 * @return Ballot[]
	 */
	public function find(array $filter = []): iterable
	{
		return parent::find($filter);
	}
}
