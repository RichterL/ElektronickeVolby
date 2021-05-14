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
use App\Repositories\VoterRepository;

class ElectionsFacade
{
	private ElectionRepository $electionRepository;
	private BallotRepository $ballotRepository;
	private VoterRepository $voterRepository;

	public function __construct(
		ElectionRepository $electionRepository,
		BallotRepository $ballotRepository,
		VoterRepository $voterRepository
	) {
		$this->electionRepository = $electionRepository;
		$this->ballotRepository = $ballotRepository;
		$this->voterRepository = $voterRepository;
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
	public function saveBallot(User $user, Ballot $ballot): void
	{
		try {
			$this->ballotRepository->beginTransaction();
			$this->ballotRepository->save($ballot);
			$this->voterRepository->update($user, $ballot->getElection());
			$this->ballotRepository->finishTransaction();
		} catch (SavingErrorException $e) {
			$this->ballotRepository->rollbackTransaction();
			throw $e;
		}
	}

	public function hasVoted(User $user, Election $election)
	{
		return $this->voterRepository->hasVoted($user, $election);
	}
}
