<?php

namespace App\Forms\Election;

interface QuestionFormFactory
{
	public function create(): QuestionForm;
}
