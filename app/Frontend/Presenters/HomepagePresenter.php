<?php
declare(strict_types=1);

namespace App\Frontend\Presenters;

use App\Frontend\Classes\ElectionsFacade;

final class HomepagePresenter extends BasePresenter
{
	private ElectionsFacade $electionsFacade;

	public function __construct(ElectionsFacade $electionsFacade)
	{
		parent::__construct();
		$this->electionsFacade = $electionsFacade;
	}

	public function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->addFilter('formatDate', function (string $date) {
			$dateTime = new \DateTime($date);
			return $dateTime->format('d.m.Y H:i');
		});
		$this->template->addFilter('formatDateUntil', function (string $date) {
			$full = false;
			$now = new \DateTime();
			$dateTime = new \DateTime($date);
			$diff = $now->diff($dateTime);
			$diff->w = floor($diff->d / 7);
			$diff->d -= $diff->w * 7;

			$string = [
				'y' => 'year',
				'm' => 'month',
				'w' => 'week',
				'd' => 'day',
				'h' => 'hour',
				'i' => 'minute',
				's' => 'second',
			];
			foreach ($string as $k => &$v) {
				if ($diff->$k) {
					$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
				} else {
					unset($string[$k]);
				}
			}

			if (!$full) {
				$string = array_slice($string, 0, 1);
			}
			if ($diff->invert) {
				return $string ? implode(', ', $string) . ' ago' : 'just now';
			}
			return $string ? 'in '. implode(', ', $string) : 'just now';
		});
	}

	public function renderDefault(): void
	{
		$this->template->availableElections = $this->electionsFacade->findVoterInVoterLists($this->getUserEntity());
	}
}
