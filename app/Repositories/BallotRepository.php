<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Election\Ballot;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\BallotMapper;

class BallotRepository extends BaseRepository
{
	private BallotMapper $ballotMapper;

	public function __construct(BallotMapper $ballotMapper)
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
