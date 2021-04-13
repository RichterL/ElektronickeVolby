<?php
declare(strict_types=1);

namespace App\Forms\Voting;

interface VotingFormFactory
{
	public function create(): VotingForm;
}
