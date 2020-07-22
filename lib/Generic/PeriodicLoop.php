<?php
/**
 * Periodic loop.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Generic;

use Amp\Promise;
use danog\Loop\ResumableSignalLoop;

/**
 * Periodic loop.
 *
 * Runs a callback at a periodic interval.
 *
 * The loop can be stopped from the outside or
 * from the inside by signaling or returning `true`.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
class PeriodicLoop extends ResumableSignalLoop
{
    /**
     * Callback.
     *
     * @var callable
     */
    private $callback;
    /**
     * Loop name.
     *
     * @var string
     */
    private $name;
    /**
     * Loop interval.
     *
     * @var ?int
     */
    private $interval;
    /**
     * Constructor.
     *
     * @param callable $callback Callback to call
     * @param string   $name     Loop name
     * @param ?int     $interval Loop interval
     */
    public function __construct(callable $callback, string $name, ?int $interval)
    {
        $this->callback = $callback;
        $this->name = $name;
        $this->interval = $interval;
    }
    /**
     * Loop implementation.
     *
     * @return \Generator
     */
    public function loop(): \Generator
    {
        $callback = $this->callback;
        while (true) {
            /** @psalm-suppress MixedAssignment */
            $result = yield $this->waitSignal($this->pause($this->interval));
            if ($result) {
                return;
            }
            /** @psalm-suppress MixedAssignment */
            $result = $callback();
            if ($result instanceof \Generator) {
                yield from $result;
            } elseif ($result instanceof Promise) {
                yield $result;
            }
        }
    }
    /**
     * Get name of the loop.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
