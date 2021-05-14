<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Election\Ballot;
use App\Models\Entities\Election\DecryptedBallot;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\EncryptedBallot;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\BallotMapper;

class BallotRepository extends BaseRepository implements TransactionableRepository
{
	private BallotMapper $ballotMapper;

	public function __construct(BallotMapper $ballotMapper)
	{
		$this->ballotMapper = $ballotMapper;
	}

	/** @return EncryptedBallot[] */
	public function findEncryptedBallots(Election $election): iterable
	{
		return $this->ballotMapper->findEncrypted($election);
	}

	/** @return DecryptedBallot[] */
	public function findDecryptedBallots(Election $election): iterable
	{
		return $this->ballotMapper->findDecrypted($election);
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

	public function beginTransaction(): void
	{
		$this->ballotMapper->beginTransaction();
	}

	public function finishTransaction(): void
	{
		$this->ballotMapper->finishTransaction();
	}

	public function rollbackTransaction(): void
	{
		$this->ballotMapper->rollbackTransaction();
	}


}
