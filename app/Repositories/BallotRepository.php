<?php

namespace App\Repositories;

use App\Models\Entities\Election\Ballot;
use App\Models\Mappers\Db\BallotMapper;
use Exception;
use Repositories\BaseRepository;

class BallotRepository extends BaseRepository
{
	private IBallotMapper $ballotMapper;

	public function __construct(IBallotMapper $ballotMapper)
	{
		$this->ballotMapper = $ballotMapper;
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Ballot $ballot): bool
	{
		return $this->ballotMapper->save($ballot);
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id): Ballot
	{
		return $this->ballotMapper->findOne(['id' => $id]);
	}
}
