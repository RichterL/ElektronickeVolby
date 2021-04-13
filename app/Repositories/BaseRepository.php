<?php

namespace Repositories;

use Nette\Caching\Cache;

class BaseRepository
{
	protected Cache $cache;

	public function setCache(\Nette\Caching\Cache $cache): void
	{
		$this->cache = $cache;
	}
}
