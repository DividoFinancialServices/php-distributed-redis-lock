<?php

namespace Divido\RedisLock;

/**
 * Class LockModel
 *
 * @author Neil Mcgibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2018, Divido
 * @package Divido\RedisLock
 */
class LockModel
{
    /**
     * @var int
     */
    private $expires;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $token;

    /**
     * LockModel constructor.
     */
    function __construct()
    {
        ;
    }

    /**
     * Set expires time (in millseconds)
     *
     * @param int $expires
     * @return LockModel
     */
    public function withExpires(int $expires) :LockModel
    {
        $c = clone($this);
        $c->expires = $expires;
        return $c;
    }

    /**
     * Get expires time (in milleseconds)
     *
     * @return int
     */
    public function getExpires() :int
    {
        return $this->expires;
    }

    /**
     * Set lock key
     *
     * @param string $key
     * @return LockModel
     */
    public function withKey(string $key) :LockModel
    {
        $c = clone($this);
        $c->key = $key;
        return $c;
    }

    /**
     * Get lock key
     *
     * @return string
     */
    public function getKey() :string
    {
        return $this->key;
    }

    /**
     * Set lock token
     *
     * @param string $token
     * @return LockModel
     */
    public function withToken(string $token) :LockModel
    {
        $c = clone($this);
        $c->token = $token;
        return $c;
    }

    /**
     * Get lock token
     *
     * @return string
     */
    public function getToken() :string
    {
        return $this->token;
    }



}
