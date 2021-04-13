<?php
declare(strict_types=1);

namespace App\Models\Factories;

use App\Models\Entities\Election\Ballot;
use App\Models\Entities\Election\DecryptedBallot;
use App\Models\Entities\Election\EncryptedBallot;
use App\Models\Mappers\ElectionMapper;

class BallotFactory
{
	private ElectionMapper $electionMapper;

	public function __construct(ElectionMapper $electionMapper)
	{
		$this->electionMapper = $electionMapper;
	}

	public function create(array $data): Ballot
	{
		$data['election'] = $this->electionMapper->findOne(['id' => $data['election']]);
		if (!empty($data['encrypted_at'])) {
			$ballot = new DecryptedBallot();
		} else {
			$ballot = new EncryptedBallot();
		}
		$ballot->setValues($data);
		return $ballot;
	}
}
