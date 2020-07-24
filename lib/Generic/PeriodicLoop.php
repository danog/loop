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
 * @template T as bool
 * @template TGenerator as \Generator<mixed,Promise|array<array-key,Promise>,mixed,Promise<T>|T>
 * @template TPromise as Promise<T>
 *
 * @template TCallable as T|TPromise|TGenerator
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
class PeriodicLoop extends ResumableSignalLoop
{
    /**
     * Callback.
     *
     * @var callable
     *
     * @psalm-var callable():TCallable
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
     * If possible, the callable will be bound to the current instance of the loop.
     *
     * @param callable $callback Callback to call
     * @param string   $name     Loop name
     * @param ?int     $interval Loop interval
     *
     * @psalm-param callable():TCallable $callback Callable to run
     */
    public function __construct(callable $callback, string $name, ?int $interval)
    {
        if ($callback instanceof \Closure) {
            try {
                $callback = $callback->bindTo($this);
            } catch (\Throwable $e) {
                // Might cause an error for wrapped object methods
            }
        }
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
            $result = $callback();
            if ($result instanceof \Generator) {
                /** @psalm-var TGenerator */
                $result = yield from $result;
            } elseif ($result instanceof Promise) {
                /** @psalm-var TPromise */
                $result = yield $result;
            }
            if ($result === true) {
                break;
            }
            /** @var ?bool */
            $result = yield $this->waitSignal($this->pause($this->interval));
            if ($result === true) {
                break;
            }
        }
    }
    /**
     * Get name of the loop, passed to the constructor.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
