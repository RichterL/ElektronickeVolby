<?php

namespace App\Repositories;

use Nette\Caching\Cache;

class BaseRepository
{
	protected Cache $cache;

	public function setCache(Cache $cache): void
	{
		$this->cache = $cache;
	}
}
