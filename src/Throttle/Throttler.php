<?php

namespace NspTeam\WebmanThrottler\Throttle;


/**
 * Class Throttler
 *
 * Uses an implementation of the Token Bucket algorithm to implement a
 * "rolling window" type of throttling that can be used for rate limiting
 * an API or any other request.
 *
 * Each "token" in the "bucket" is equivalent to a single request
 * for the purposes of this implementation.
 *
 * @see https://en.wikipedia.org/wiki/Token_bucket
 */
class Throttler implements ThrottlerInterface
{
    /**
     * Container for throttle counters.
     *
     * @var object
     */
    protected $cache;

    /**
     * The number of seconds until the next token is available.
     *
     * @var int
     */
    protected $tokenTime = 0;

    /**
     * The prefix applied to all keys to
     * minimize potential conflicts.
     *
     * @var string
     */
    protected $prefix = 'throttler_';

    /**
     * Timestamp to use (during testing)
     *
     * @var int
     */
    protected $testTime;

    public function __construct(object $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key The name of the bucket
     * @return $this
     */
    public function remove(string $key): self
    {
        $tokenName = $this->prefix . $key;

        $this->cache->delete($tokenName);
        $this->cache->delete($tokenName . 'Time');

        return $this;
    }

    /**
     * Return the test time, defaulting to current.
     */
    private function time(): int
    {
        return $this->testTime ?? time();
    }

    /**
     * Used during testing to set the current timestamp to use.
     *
     * @return $this
     */
    public function setTestTime(int $time): self
    {
        $this->testTime = $time;

        return $this;
    }

    /**
     * Restricts the number of requests made by a single IP address within
     * a set number of seconds.
     *
     * Example:
     *
     *  if (! $throttler->check($request->getRemoteIp(), 60, MINUTE)) {
     *      die('You submitted over 60 requests within a minute.');
     *  }
     *
     * @param string $key The name to use as the "bucket" name.
     * @param int $capacity The number of requests the "bucket" can hold
     * @param int $seconds The time it takes the "bucket" to completely refill
     * @param int $cost The number of tokens this action uses.
     *
     * @internal param int $maxRequests
     */
    public function check(string $key, int $capacity, int $seconds, int $cost): bool
    {
        $tokenName = $this->prefix . $key;

        // Number of tokens to add back per second
        $rate = $capacity / $seconds;
        // Number of seconds to get one token
        $refresh = 1 / $rate;

        // Check to see if the bucket has even been created yet.
        if (($tokens = $this->cache->get($tokenName)) === null) {
            // If it hasn't been created, then we'll set it to the maximum
            // capacity - 1, and save it to the cache.
            $tokens = $capacity - $cost;
            $this->cache->set($tokenName, $tokens, $seconds);
            $this->cache->set($tokenName . 'Time', $this->time(), $seconds);

            $this->tokenTime = 0;

            return true;
        }

        // If $tokens > 0, then we need to replenish the bucket
        // based on how long it's been since the last update.
        $throttleTime = $this->cache->get($tokenName . 'Time');
        $elapsed = $this->time() - $throttleTime;

        // Add tokens based up on number per second that
        // should be refilled, then checked against capacity
        // to be sure the bucket didn't overflow.
        $tokens += $rate * $elapsed;
        $tokens = $tokens > $capacity ? $capacity : $tokens;

        // If $tokens >= 1, then we are safe to perform the action, but
        // we need to decrement the number of available tokens.
        if ($tokens >= 1) {
            $tokens -= $cost;
            $this->cache->set($tokenName, $tokens, $seconds);
            $this->cache->set($tokenName . 'Time', $this->time(), $seconds);

            $this->tokenTime = 0;

            return true;
        }

        // How many seconds till a new token is available.
        // We must have a minimum wait of 1 second for a new token.
        // Primarily stored to allow devs to report back to users.
        $newTokenAvailable = (int)($refresh - $elapsed - $refresh * $tokens);
        $this->tokenTime = max(1, $newTokenAvailable);

        return false;
    }

    /**
     * Returns the number of seconds until the next available token will
     * be released for usage.
     * @return int
     */
    public function getTokenTime(): int
    {
        return $this->tokenTime;
    }
}