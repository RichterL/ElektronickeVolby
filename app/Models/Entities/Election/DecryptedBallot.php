<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;


use App\Models\Entities\User\UserId;

/**
 * @property string $decryptedData
 * @property \DateTimeInterface $decryptedAt
 * @property UserId $decryptedBy
 */
class DecryptedBallot extends Ballot
{
	protected string $decryptedData;
	protected \DateTimeInterface $decryptedAt;
	protected UserId $decryptedBy;
	private ?array $unpacked = null;

	public function unpackData(): array
	{
		if ($this->unpacked === null) {
			$this->unpacked = json_decode($this->decryptedData, true, 512, JSON_THROW_ON_ERROR);
		}
		return $this->unpacked;
	}
}
