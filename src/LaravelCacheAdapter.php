<?php
/**
 * Laravel Cache Adapter for AWS Credential Caching.
 *
 * @author    Luke Waite <lwaite@gmail.com>
 * @copyright 2017 Luke Waite
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link      https://github.com/lukewaite/laravel-aws-cache-adapter
 */

namespace LukeWaite\LaravelAwsCacheAdapter;

use Aws\CacheInterface;
use Illuminate\Cache\CacheManager;

class LaravelCacheAdapter implements CacheInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $store;

    /**
     * @var CacheManager
     */
    private $manager;

    public function __construct(CacheManager $manager, $store, $prefix = null)
    {
        $this->manager = $manager;
        $this->store = $store;
        $this->prefix = 'aws_credentials_'.$prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->getCache()->get($this->generateKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->getCache()->forget($this->generateKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 0)
    {
        $this->getCache()->put($this->generateKey($key), $value, $this->convertTtl($ttl));
    }

    /**
     * Generate a cache key which incorporates the prefix.
     *
     * @return string
     */
    protected function generateKey($key)
    {
        return $this->prefix.$key;
    }

    /**
     * The AWS CacheInterface takes input in seconds, but the Laravel Cache classes use minutes. To support
     * this intelligently, we round up to one minute for any value less than 60 seconds, and round down to
     * the nearest whole minute for any value over one minute. First, if the passed in TTL is 0 we return
     * 0 to allow an unlimited cache lifetime.
     *
     * @return float|int
     */
    protected function convertTtl($ttl)
    {
        if ($ttl == 0) {
            return 0;
        }

        $minutes = floor($ttl / 60);

        if ($minutes == 0) {
            return 1;
        } else {
            return $minutes;
        }
    }

    /**
     * Returns the configured Laravel Cache Store
     *
     * @return mixed
     */
    protected function getCache()
    {
        return $this->manager->store($this->store);
    }
}
