<?php
declare(strict_types=1);

namespace App\Forms\Election;

interface QuestionFormFactory
{
	public function create(): QuestionForm;
}
