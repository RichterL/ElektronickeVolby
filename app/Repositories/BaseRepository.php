<?php

namespace Repositories;

class BaseRepository
{
	protected $cache;

	public function setCache(\Nette\Caching\Cache $cache)
	{
		$this->cache = $cache;
	}
}
