<?php

namespace Divido\RedisLock;

/**
 * Class RedisLock
 *
 * @author Ronny Lopez <ronny@tangotree.io>
 * @author Neil Mcgibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2018, Divido
 * @package Divido\RedisLock
 */
class RedisLock
{
    /**
     * @var float
     */
    private $clockDriftFactor = 0.01;

    /**
     * @var int
     */
    private $quorum;

    /**
     * @var \Redis[]
     */
    private $nodes = [];

    /**
     * RedisLock constructor.
     *
     * @param \Redis[] $nodes
     * @param int $retryDelay
     * @param int $retryCount
     */
    function __construct(array $nodes)
    {
        $this->nodes = $nodes;
        $this->quorum  = min(count($nodes), (intval(count($nodes) / 2) + 1));
    }

    /**
     * Acquire atomic lock for specified key
     *
     * @param string $key The key name to lock
     * @param int $ttl Lock expiry time, in Milliseconds
     * @return LockModel
     * @throws UnableToAcquireLockException
     */
    public function lock(string $key, int $ttl, int $retryDelay = 200, int $retryCount = 3)
    {
        // Generate a unique lock token
        $token = uniqid();
        $retry = $retryCount;

        do {
            $n = 0;
            $startTime = microtime(true) * 1000;

            foreach ($this->nodes as $node) {
                if ($this->acquireLockOnNode($node, $key, $token, $ttl)) {
                    $n++;
                }
            }

            # Add 2 milliseconds to the drift to account for Redis expires
            # precision, which is 1 millisecond, plus 1 millisecond min drift
            # for small TTLs.
            $drift = ($ttl * $this->clockDriftFactor) + 2;

            $expiresTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;
            $lock = (new LockModel())
                ->withKey($key)
                ->withToken($token)
                ->withExpires($expiresTime);

            if ($n >= $this->quorum && $expiresTime > 0) {
                return $lock;
            } else {
                $this->unlock($lock);
            }

            $retry--;
            if ($retry > 0) {
                // Wait a random delay before to retry
                $delay = mt_rand(floor($retryDelay / 2), $retryDelay);
                usleep($delay * 1000);
            }

        } while ($retry > 0);

        throw new UnableToAcquireLockException();
    }

    /**
     * Release the specified lock
     *
     * @param LockModel $lock
     */
    public function unlock(LockModel $lock)
    {
        foreach ($this->nodes as $node) {
            $this->releaseLockOnNode($node, $lock->getKey(), $lock->getToken());
        }
    }

    /**
     * @param \Redis $node
     * @param string $key
     * @param string $token
     * @param int $ttl
     * @return bool
     */
    private function acquireLockOnNode(\Redis $node, string $key, string $token, int $ttl)
    {
        return $node->set($key, $token, ['NX', 'PX' => $ttl]);
    }

    /**
     * @param \Redis $node
     * @param string $key
     * @param string $token
     * @return mixed
     */
    private function releaseLockOnNode(\Redis $node, string $key, string $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return $node->eval($script, [$key, $token], 1);
    }
}
