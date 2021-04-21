<?php
declare(strict_types=1);

namespace App\Backend\Classes\VoteCounting;

use App\Backend\Classes\VoteCounting\Exception\DecryptionException;
use App\Backend\Classes\VoteCounting\Exception\VerificationException;
use App\Models\Entities\Election\Ballot;
use App\Models\Entities\Election\DecryptedBallot;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\EncryptedBallot;
use App\Models\Entities\User\UserId;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Repositories\BallotRepository;
use App\Repositories\ElectionRepository;
use Nette\InvalidStateException;
use Nette\Security\IIdentity;
use Nette\Security\User;
use phpseclib3\Crypt\Hash;
use Tracy\Logger;

class BallotDecryptor
{
	private Election $election;
	private ?IIdentity $user;
	/** @var EncryptedBallot[] $ballots */
	private array $ballots = [];
	/** @var DecryptedBallot[] $decryptedBallots */
	private array $decryptedBallots = [];
	private ElectionRepository $electionRepository;
	private BallotRepository $ballotRepository;
//	private BallotFactory $ballotFactory;

	public function __construct(
		ElectionRepository $electionRepository,
		BallotRepository $ballotRepository,
		User $user
//		BallotFactory $ballotFactory
	) {
		$this->electionRepository = $electionRepository;
		$this->ballotRepository = $ballotRepository;
		$this->user = $user->getIdentity();
//		$this->ballotFactory = $ballotFactory;
	}

	public function setElection(Election $election): BallotDecryptor
	{
		$this->election = $election;
		return $this;
	}

	private function loadBallots(): void
	{
		$ballots = $this->ballotRepository->findEncryptedBallots($this->election);
//		for ($i = 0; $i < 500; $i++) {
//			foreach ($ballots as $ballot) {
//				$this->ballots[] = $ballot;
//			}
//		}
		$this->ballots = $this->ballotRepository->findEncryptedBallots($this->election);
	}

	public function decryptBallots(): array
	{
		$this->loadBallots();
		foreach ($this->ballots as $ballot) {
			try {
				$this->verify($ballot);
				$decrypted = $this->decrypt($ballot);
//				$this->ballotRepository->save($decrypted);

			} catch (DecryptionException | SavingErrorException | VerificationException $e) {
				$this->log($e, Logger::WARNING, $ballot);
			}
		}
		return $this->decryptedBallots;
	}

	/**
	 * @throws DecryptionException
	 */
	private function decrypt(EncryptedBallot $ballot): DecryptedBallot
	{
		try {
			$decryptingKey = $this->decryptKey($ballot->encryptedKey);
			$decrypted = $decryptingKey->decrypt(base64_decode($ballot->encryptedData));
			$decryptedBallot = new DecryptedBallot();
			$decryptedBallot->setId($ballot->getId())
				->setValues([
					'decryptedAt' => new \DateTime(),
					'decryptedBy' => UserId::fromValue($this->user->getId()),
					'decryptedData' => $decrypted,
				]);
			$this->decryptedBallots[] = $decryptedBallot;
			return $decryptedBallot;
		} catch (\Exception $e) {
			throw new DecryptionException('Decryption error: ' . $e->getMessage());
		}
	}

	/**
	 * @throws DecryptionException
	 */
	private function decryptKey(string $encryptedKey): DecryptingKey
	{
		static $decryptingKey;
		if ($decryptingKey === null) {
			$decryptingKey = $this->election->getPrivateEncryptionKey();
			if ($decryptingKey === null) {
				throw new InvalidStateException('Decrypting key has not been set yet.');
			}
		}

		try {
			$decrypted = $decryptingKey->decrypt(base64_decode($encryptedKey));
			['key' => $key, 'iv' => $iv] = json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);
			return new DecryptingKey($key, $iv);
		} catch (\Exception $e) {
			throw new DecryptionException('Decrypting AES key failed');
		}
	}

	/**
	 * @throws VerificationException
	 */
	private function verify(EncryptedBallot $ballot): void
	{
		$hash = $this->hash($ballot->encryptedKey);
		if (bin2hex($hash) !== $ballot->hash) {
			throw new VerificationException('hash does not match the stored value');
		}
		$signature = $this->sign($hash);
		if (bin2hex($signature) !== $ballot->signature) {
			throw new VerificationException('signature does not match the stored value');
		}
	}

	private function hash(string $message): string
	{
		static $sha;
		if ($sha === null) {
			$sha = new Hash('sha256');
		}
		return $sha->hash($message);
	}

	private function sign(string $message): string
	{
		static $key;
		if ($key === null) {
			$key = $this->election->getPrivateSigningKey();
		}
		return $key->decrypt($message);
	}

	private function log(\Throwable $throwable, string $level = Logger::WARNING, Ballot $ballot = null): void
	{
		static $logger;
		if ($logger === null) {
			$logger = new Logger(LOG_DIR . '/decryptionLog');
		}
		$message = $throwable->getMessage();
		$message .= $ballot ? ' ballotId: ' . $ballot->getId() : '';
		$logger->log($message, $level);
	}
}
