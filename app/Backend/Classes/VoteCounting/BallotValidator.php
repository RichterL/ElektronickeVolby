<?php
declare(strict_types=1);

namespace App\Backend\Classes\VoteCounting;

use App\Backend\Classes\VoteCounting\Exception\ValidationException;
use App\Models\Entities\Election\AnswerCollection;
use App\Models\Entities\Election\Ballot;
use App\Models\Entities\Election\DecryptedBallot;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\Question;
use App\Repositories\BallotRepository;
use Tracy\Logger;

class BallotValidator
{
	private Election $election;
	/** @var DecryptedBallot[] $ballots */
	private array $ballots;
	private BallotRepository $ballotRepository;

	public function __construct(BallotRepository $repository)
	{
		$this->ballotRepository = $repository;
	}

	public function setElection(Election $election): BallotValidator
	{
		$this->election = $election;
		return $this;
	}

	public function validateBallots(): array
	{
		$this->ballots = $this->ballotRepository->findDecryptedBallots($this->election);
		return $this->validate($this->ballots);
	}

	/** @param DecryptedBallot[] $ballots */
	private function validate(array $ballots): array
	{
		$valid = $invalid = $error = [];
		foreach ($ballots as $ballot) {
			try {
				$data = $ballot->unpackData();
				$this->checkElection((int) $data['electionId']);
				$this->checkQuestions($data['questions']);
				$valid[] = $ballot;
			} catch (\JsonException $e) {
				$error[] = $ballot;
				$this->log($e, Logger::CRITICAL, $ballot);
			} catch (ValidationException $e) {
				$invalid[] = $ballot;
				$this->log($e, Logger::WARNING, $ballot);
			}
		}
		return [$valid, $invalid, $error];
	}

	/** @return Question[] */
	private function getQuestions(): array
	{
		static $questions;
		if ($questions === null) {
			foreach ($this->election->getQuestions() as $question) {
				$questions[$question->getId()] = $question;
			}
		}
		return $questions;
	}

	private function getAnswers(int $questionId): array
	{
		static $answers;
		if ($answers === null) {
			foreach ($this->getQuestions() as $qId => $question) {
				$answers[$qId] = $question->getAnswers()->getIdValuePairs();
			}
		}
		return $answers[$questionId];
	}

	/**
	 * @throws ValidationException
	 */
	private function checkElection(int $value): void
	{
		static $electionId;
		if ($electionId === null) {
			$electionId = $this->election->getId();
		}
		if ($electionId !== $value) {
			throw new ValidationException('Wrong election id');
		}
	}

	/**
	 * @throws ValidationException
	 */
	private function checkQuestions(array $tested): void
	{
		foreach ($this->getQuestions() as $qId => $question) {
			if (empty($tested[$qId]) && !$question->required) {
				continue;
			}
			if ($question->required && empty($tested[$qId])) {
				throw new ValidationException('missing required question');
			}
			$answerCount = count($tested[$qId]);
			if (($question->getMin() > $answerCount) || ($question->getMax() < $answerCount)) {
				throw new ValidationException('Wrong number of answers');
			}
			$this->checkAnswers($qId, $tested[$qId]);

		}
	}

	/**
	 * @throws ValidationException
	 */
	private function checkAnswers(int $questionId, array $tested): void
	{
		$answers = $this->getAnswers($questionId);
		foreach ($tested as $key => $value) {
			if (!array_key_exists($key, $answers) || $answers[$key] !== $value) {
				throw new ValidationException('answer does not match');
			}
		}
	}

	private function log(\Throwable $throwable, string $level = Logger::WARNING, Ballot $ballot = null): void
	{
		static $logger;
		if ($logger === null) {
			$logger = new Logger(LOG_DIR . '/validationLog');
		}
		$message = $throwable->getMessage();
		$message .= $ballot ? ' ballotId: ' . $ballot->getId() : '';
		$logger->log($message, $level);
	}
}
