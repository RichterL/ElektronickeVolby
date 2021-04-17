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
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA\PublicKey;

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
 * @property string $publicKey
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
	protected ?string $publicKey = null;

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

	public function getRawPublicKey(): ?string
	{
		return $this->publicKey;
	}

	/**
	 * @throws Exception
	 */
	public function getPublicKey(): PublicKey
	{
		$key = PublicKeyLoader::load($this->publicKey);
		if ($key instanceof PublicKey) {
			return $key;
		}
		throw new Exception('Loading the public key failed');
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
