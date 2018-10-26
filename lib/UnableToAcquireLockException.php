<?php

namespace Divido\RedisLock;

/**
 * Class UnableToAcquireLockException
 *
 * @author Neil Mcgibbon <neil.mcgibbon@divido.com>
 * @copyright (c) 2018, Divido
 * @package Divido\RedisLock
 */
class UnableToAcquireLockException extends \Exception
{
    public function __construct()
    {
        parent::__construct("unable to acquire redis lock");
    }
}
