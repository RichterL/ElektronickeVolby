<?php
declare(strict_types=1);

namespace App\Frontend\Classes;

use App\Models\Entities\Election\Ballot;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Repositories\BallotRepository;
use App\Models\Entities\Election\Election;
use App\Models\Entities\User;
use App\Repositories\ElectionRepository;

class ElectionsFacade
{
	private ElectionRepository $electionRepository;
	private BallotRepository $ballotRepository;

	public function __construct(
		ElectionRepository $electionRepository,
		BallotRepository $ballotRepository
	) {
		$this->electionRepository = $electionRepository;
		$this->ballotRepository = $ballotRepository;
	}

	public function getAllElections(): array
	{
		return $this->electionRepository->findAll();
	}

	public function getAllActiveElections(): array
	{
		return $this->electionRepository->findActive();
	}

	public function findVoterInVoterLists(User $user): array
	{
		return $this->electionRepository->findRelated($user);
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function getElectionById(int $electionId): Election
	{
		return $this->electionRepository->findById($electionId);
	}

	/**
	 * @throws SavingErrorException
	 */
	public function saveBallot(Ballot $ballot): bool
	{
		return $this->ballotRepository->save($ballot);
	}
}
