<?php

/**
 * Generic loop.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Generic;

use Amp\Promise;
use danog\Loop\ResumableSignalLoop;

/**
 * Generic loop, runs single callable.
 *
 * The return value of the callable can be:
 * * A number - the loop will be paused for the specified number of seconds
 * * GenericLoop::STOP - The loop will stop
 * * GenericLoop::PAUSE - The loop will pause forever (or until loop is `resumed()`
 *                        from outside the loop)
 * * GenericLoop::CONTINUE - Return this if you want to rerun the loop immediately
 *
 * If the callable does not return anything,
 * the loop will behave is if GenericLoop::PAUSE was returned.
 *
 * The loop can be stopped from the outside by signaling `true`.
 *
 * @template T as int|null
 * @template TGenerator as \Generator<mixed,Promise|array<array-key,Promise>,mixed,Promise<T>|T>
 * @template TPromise as Promise<T>
 *
 * @template TCallable as T|TPromise|TGenerator
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
class GenericLoop extends ResumableSignalLoop
{
    /**
     * Stop the loop.
     */
    const STOP = -1;
    /**
     * Pause the loop.
     */
    const PAUSE = null;
    /**
     * Rerun the loop.
     */
    const CONTINUE = 0;
    /**
     * Callable.
     *
     * @var callable
     *
     * @psalm-var callable():TCallable
     */
    protected $callable;
    /**
     * Loop name.
     *
     * @var string
     */
    protected $name;
    /**
     * Constructor.
     *
     * If possible, the callable will be bound to the current instance of the loop.
     *
     * @param callable $callable Callable to run
     * @param string   $name     Loop name
     *
     * @psalm-param callable():TCallable $callable Callable to run
     */
    public function __construct(callable $callable, string $name)
    {
        if ($callable instanceof \Closure) {
            try {
                $callable = $callable->bindTo($this);
            } catch (\Throwable $e) {
                // Might cause an error for wrapped object methods
            }
        }
        $this->callable = $callable;
        $this->name = $name;
    }
    /**
     * Loop implementation.
     *
     * @return \Generator
     */
    public function loop(): \Generator
    {
        $callable = $this->callable;
        while (true) {
            /** @psalm-var ?int|TGenerator|TPromise */
            $timeout = $callable();
            if ($timeout instanceof \Generator) {
                /** @psalm-var ?int */
                $timeout = yield from $timeout;
            } elseif ($timeout instanceof Promise) {
                /** @psalm-var ?int */
                $timeout = yield $timeout;
            }
            if ($timeout === self::PAUSE) {
                $this->reportPause(0);
            } elseif ($timeout > 0) {
                $this->reportPause($timeout);
            }
            /** @psalm-suppress MixedArgument */
            if ($timeout === self::STOP || yield $this->waitSignal($this->pause($timeout))) {
                break;
            }
        }
    }
    /**
     * Report pause, can be overriden for logging.
     *
     * @param integer $timeout Pause duration, 0 = forever
     *
     * @return void
     */
    protected function reportPause(int $timeout): void
    {
    }
    /**
     * Get loop name, provided to constructor.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
