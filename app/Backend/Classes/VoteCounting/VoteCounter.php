<?php
declare(strict_types=1);

namespace App\Backend\Classes\VoteCounting;

use App\Models\Entities\Election\DecryptedBallot;
use App\Models\Entities\Election\Election;
use App\Repositories\BallotRepository;
use App\Repositories\ElectionRepository;
use Nette\Security\User;

class VoteCounter
{
	private array $counter = [];
	private Election $election;
	private User $user;
	private BallotDecryptor $ballotDecryptor;
	private BallotValidator $ballotValidator;
	private BallotRepository $ballotRepository;

	public function __construct(
		BallotDecryptor $ballotDecryptor,
		BallotValidator $ballotValidator,
		BallotRepository $ballotRepository,
		User $user
	) {
		$this->ballotDecryptor = $ballotDecryptor;
		$this->ballotValidator = $ballotValidator;
		$this->ballotRepository = $ballotRepository;
		$this->user = $user;
	}

	public function processBallots(Election $election): array
	{
		$this->election = $election;
//		$ballots = $this->ballotRepository->findEncryptedBallots($this->election);
		$decrypted = $this->ballotDecryptor->setElection($election)->decryptBallots();
		[$valid, $invalid, $errors] = $this->ballotValidator->setElection($election)->validate($decrypted);
		$this->prepareCounter();
		$this->countResults($valid);
		return $this->counter;
	}

	private function prepareCounter()
	{
		foreach ($this->election->getQuestions() as $question) {
			$this->counter[$question->getId()] =
				array_fill_keys(array_keys($question->getAnswers()->getIdValuePairs()), 0);
		}
	}

	/** @var DecryptedBallot[] $ballots */
	private function countResults(iterable $ballots)
	{
		foreach ($ballots as $ballot) {
			$data = $ballot->unpackData();
			foreach ($data['questions'] as $questionId => $answers) {
				foreach ($answers as $answerId => $value) {
					$this->counter[$questionId][$answerId]++;
				}
			}
		}
	}
}
