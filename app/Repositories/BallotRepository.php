<?php

namespace App\Repositories;

use App\Models\Entities\Election\Ballot;
use App\Models\Mappers\Db\BallotMapper;
use Exception;
use Repositories\BaseRepository;

class BallotRepository extends BaseRepository
{
	private BallotMapper $ballotMapper;

	public function __construct(BallotMapper $ballotMapper)
	{
		$this->ballotMapper = $ballotMapper;
	}

	/**
	 * @throws Exception
	 */
	public function save(Ballot $ballot): bool
	{
		return $this->ballotMapper->save($ballot);
	}

	public function findById(int $id): Ballot
	{
		return $this->ballotMapper->findOne(['id' => $id]);
	}
}
