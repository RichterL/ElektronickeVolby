<?php

namespace App\Forms\Voting;

interface VotingFormFactory
{
	public function create(): VotingForm;
}
