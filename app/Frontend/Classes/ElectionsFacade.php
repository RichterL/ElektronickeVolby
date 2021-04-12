<?php

namespace App\Frontend\Classes;

use App\Models\Entities\Election\Ballot;
use App\Repositories\BallotRepository;
use Exception;
use Models\Entities\Election\Election;
use Models\Entities\User;
use Repositories\ElectionRepository;

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

	public function getElectionById(int $electionId): ?Election
	{
		return $this->electionRepository->findById($electionId);
	}

	/**
	 * @throws Exception
	 */
	public function saveBallot(Ballot $ballot): bool
	{
		return $this->ballotRepository->save($ballot);
	}
}
