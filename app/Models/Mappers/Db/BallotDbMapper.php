<?php

namespace App\Models\Mappers\Db;

use App\Models\Entities\Election\Ballot;
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
	];

	protected string $table = Tables::BALLOT;
	private BallotFactory $ballotFactory;

	public function __construct(BallotFactory $ballotFactory)
	{
		$this->ballotFactory = $ballotFactory;
	}

	public function create(array $data = []): Ballot
	{
		foreach (self::MAP as $property => $key) {
			$tmp[$property] = $data[$key];
		}
		return $this->ballotFactory->create($data);
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Ballot $ballot): bool
	{
		return $this->saveWithId($ballot);
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
