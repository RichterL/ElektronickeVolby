<?php
declare(strict_types=1);

namespace App\Repositories;

use Nette\Caching\Cache;
use Nette\Caching\Storage;

class BaseRepository
{
	protected Cache $cache;
	public const CACHE_NAMESPACE = 'global';

	public function initCache(Storage $storage): void
	{
		$this->cache = new Cache($storage, static::CACHE_NAMESPACE);
	}

	/** @param string[] $namespaces */
	public function invalidate(array $namespaces = []): void
	{
		$this->cache->clean([Cache::NAMESPACES => $namespaces ?: [static::CACHE_NAMESPACE]]);
	}
}
