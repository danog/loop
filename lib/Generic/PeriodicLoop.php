<?php declare(strict_types=1);
/**
 * Periodic loop.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Generic;

/**
 * Periodic loop.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
class PeriodicLoop extends GenericLoop
{
    /**
     * Constructor.
     *
     *
     * Runs a callback at a periodic interval.
     *
     * The loop can be stopped from the outside by calling stop()
     * and from the inside by returning `true`.
     *
     * @param callable():bool $callback Callable to run
     * @param string   $name     Loop name
     * @param ?float   $interval Loop interval
     */
    public function __construct(callable $callback, string $name, ?float $interval)
    {
        parent::__construct(static function () use ($callback, $interval): ?float {
            if ($callback() === true) {
                return GenericLoop::STOP;
            }
            return $interval;
        }, $name);
    }
}
