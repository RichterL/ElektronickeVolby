<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

use App\Models\Traits\Entity\HasId;
use App\Core\Utils\Constants;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Entities\User;
use Nette\InvalidStateException;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey;
use phpseclib3\Crypt\RSA\PublicKey;
use phpseclib3\Exception\NoKeyLoadedException;

/**
 * @property int|null $id
 * @property string $title
 * @property string $description
 * @property bool $active
 * @property bool $secret
 * @property DateTimeInterface $start
 * @property DateTimeInterface $end
 * @property DateTimeInterface $createdAt
 * @property User $createdBy
 * @property Question[] $questions
 * @property string|null $encryptionKey
 * @property string $signingKey
 */
class Election extends Entity implements IdentifiedById
{
	protected string $title;
	protected string $description;
	protected bool $active;
	protected bool $secret;
	protected DateTimeInterface $start;
	protected DateTimeInterface $end;
	protected DateTimeInterface $createdAt;
	protected User $createdBy;
	protected iterable $questions = [];
	protected ?string $encryptionKey = null;
	protected string $signingKey;

	use HasId;

	public function isRunning(): bool
	{
		$now = new \DateTime();
		return $now >= $this->start && $now < $this->end && $this->active;
	}

	public function isActive(): bool
	{
		return $this->active;
	}

	/**
	 * @param string|DateTimeInterface $from
	 */
	public function setStart($from): self
	{
		$this->start = $this->getDateTime($from);
		return $this;
	}

	/**
	 * @param string|DateTimeInterface $to
	 */
	public function setEnd($to): self
	{
		$this->end = $this->getDateTime($to);
		return $this;
	}

	/**
	 * @param string|DateTimeInterface $datetime
	 */
	public function setCreatedAt($datetime): self
	{
		$this->createdAt = $this->getDateTime($datetime);
		return $this;
	}

	public function getQuestions(): iterable
	{
		return $this->questions;
	}

	public function setQuestions(iterable $questions): self
	{
		/** @var Question $question */
		foreach ($questions as $question) {
			$question->setElection($this);
		}
		$this->questions = $questions;
		return $this;
	}

	public function setActive(bool $active = true): Election
	{
		$this->active = $active;
		return $this;
	}

	public function getPublicEncryptionKey(): ?PublicKey
	{
		if ($this->encryptionKey === null) {
			return null;
		}
		try {
			$key = PublicKeyLoader::load($this->encryptionKey);
			if ($key instanceof PublicKey) {
				return $key;
			}
			throw new NoKeyLoadedException();
		} catch (NoKeyLoadedException $e) {
			throw new \RuntimeException('Loading the encryption key failed');
		}
	}

	public function getPublicSigningKey(): PublicKey
	{
		try {
			$private = $this->getPrivateSigningKey();
			return $private->getPublicKey();
		} catch (Exception $e) {
			throw new \RuntimeException('Loading the public signing key failed.');
		}
	}

	public function getPrivateSigningKey(): PrivateKey
	{
		try {
			$key = PublicKeyLoader::load($this->signingKey);
			if ($key instanceof PrivateKey) {
				return $key;
			}
			throw new NoKeyLoadedException();
		} catch (NoKeyLoadedException $e) {
			throw new \RuntimeException('Loading the private signing key failed');
		}
	}

	public function setSigningKey(string $signingKey): void
	{
		if (isset($this->signingKey)) {
			throw new InvalidStateException('Signing key cannot be changed');
		}
		$key = PublicKeyLoader::load($signingKey);
		if (!$key instanceof PrivateKey) {
			throw new InvalidArgumentException('Invalid RSA private key.');
		}
		$this->signingKey = $signingKey;
	}

	public function setEncryptionKey(?string $encryptionKey): void
	{
		if ($encryptionKey === null) {
			return;
		}
		if (isset($this->encryptionKey) && ($this->isRunning() || $this->isActive())) {
			throw new InvalidStateException('Cannot change encryption key while election is active or running!');
		}
		$key = PublicKeyLoader::load($encryptionKey);
		if (isset($this->signingKey)) {
			$signingKey = $this->getPublicSigningKey();
			if ($signingKey == $key) {
				throw new InvalidArgumentException('Cannot use the same key for encryption and signing!');
			}
		}
		if (!$key instanceof PublicKey) {
			throw new InvalidArgumentException('Invalid RSA public key.');
		}
		$this->encryptionKey = $encryptionKey;
	}

	public function withSigningKey(): Election
	{
		if ($this->getId() === null) {
			$new = clone $this;
			$new->setSigningKey((string) RSA::createKey());
			return $new;
		}
		throw new InvalidStateException('Singing key cannot be changed once set.');
	}

	/**
	 * @param string|DateTimeInterface $value
	 */
	private function getDateTime($value): DateTimeInterface
	{
		if (is_string($value)) {
			$value = \DateTime::createFromFormat(Constants::DATETIME_FORMAT, $value);
		}
		if (!$value instanceof DateTimeInterface) {
			throw new InvalidArgumentException('Datetime format not supported, use DateTime or string format ' . Constants::DATETIME_FORMAT);
		}
		return $value;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'title' => $this->title,
			'description' => $this->description,
			'active' => $this->active,
			'secret' => $this->secret,
			'start' => $this->start->format(Constants::DATETIME_FORMAT),
			'end' => $this->end->format(Constants::DATETIME_FORMAT),
			'createdAt' => $this->createdAt->format(Constants::DATETIME_FORMAT),
			'createdBy' => $this->createdBy->getId(),
		];
	}
}
