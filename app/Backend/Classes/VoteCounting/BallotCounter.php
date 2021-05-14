<?php
declare(strict_types=1);

namespace App\Backend\Classes\VoteCounting;

use App\Models\Entities\Election\Ballot;
use App\Models\Entities\Election\DecryptedBallot;
use App\Models\Entities\Election\Election;
use App\Models\Entities\User\UserId;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Repositories\BallotRepository;
use App\Repositories\ElectionRepository;
use Nette\Security\User;
use Tracy\Logger;

class BallotCounter
{
	private array $counter = [
			'valid' => 0,
			'invalid' => 0,
			'error' => 0,
			'voters' => 0,
		];
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
		$decrypted = $this->ballotDecryptor->setElection($election)->decryptBallots();
		[$valid, $invalid, $errors] = $this->ballotValidator->setElection($election)->validateBallots();
		$this->counter = [
			'valid' => count($valid),
			'invalid' => count($invalid),
			'error' => count($errors),
		];
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

	/**
	 * @var DecryptedBallot[] $ballots
	 */
	private function countResults(iterable $ballots)
	{
		try {
			foreach ($ballots as $ballot) {
				$data = $ballot->unpackData();
				foreach ($data['questions'] as $questionId => $answers) {
					foreach ($answers as $answerId => $value) {
						$this->counter[$questionId][$answerId]++;
					}
				}
				$ballot->setCountedAt(new \DateTime())
					->setCountedBy(UserId::fromValue($this->user->getId()));
				$this->ballotRepository->save($ballot);
			}
		} catch (\JsonException $e) {
			$this->log($e, Logger::CRITICAL, $ballot);
		} catch (SavingErrorException $e) {
			$this->log($e, Logger::WARNING, $ballot);
		}
	}

	private function log(\Throwable $throwable, string $level = Logger::WARNING, Ballot $ballot = null): void
	{
		static $logger;
		if ($logger === null) {
			$logger = new Logger(LOG_DIR . '/countingLog');
		}
		$message = $throwable->getMessage();
		$message .= $ballot ? ' ballotId: ' . $ballot->getId() : '';
		$logger->log($message, $level);
	}
}
