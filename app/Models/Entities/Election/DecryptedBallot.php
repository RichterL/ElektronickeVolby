<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;


use App\Models\Entities\User\UserId;

/**
 * @property string $decryptedData
 * @property \DateTimeInterface $decryptedAt
 * @property UserId $decryptedBy
 * @property UserId $countedBy
 * @property \DateTimeInterface $countedAt;
 */
class DecryptedBallot extends Ballot
{
	protected string $decryptedData;
	protected \DateTimeInterface $decryptedAt;
	protected UserId $decryptedBy;
	protected ?UserId $countedBy = null;
	protected ?\DateTimeInterface $countedAt = null;
	private ?array $unpacked = null;

	public function unpackData(): array
	{
		if ($this->unpacked === null) {
			$this->unpacked = json_decode($this->decryptedData, true, 512, JSON_THROW_ON_ERROR);
		}
		return $this->unpacked;
	}

	public function setCountedBy(?UserId $countedBy): DecryptedBallot
	{
		$this->countedBy = $countedBy;
		return $this;
	}

	public function setCountedAt(?\DateTimeInterface $countedAt): DecryptedBallot
	{
		$this->countedAt = $countedAt;
		return $this;
	}
}
