<?php

namespace App\Frontend\Classes;

use Models\Entities\Election\Election;
use Models\Entities\User;
use Repositories\ElectionRepository;

class ElectionsFacade
{
	private ElectionRepository $electionRepository;

	public function __construct(ElectionRepository $electionRepository)
	{
		$this->electionRepository = $electionRepository;
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
}
