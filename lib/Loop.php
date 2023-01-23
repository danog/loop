<?php declare(strict_types=1);

/**
 * Generic loop.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop;

use Revolt\EventLoop;
use Stringable;

/**
 * Generic loop, runs single callable.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
abstract class Loop implements Stringable
{
    /**
     * Stop the loop.
     */
    const STOP = -1.0;
    /**
     * Pause the loop.
     */
    const PAUSE = null;
    /**
     * Rerun the loop.
     */
    const CONTINUE = 0.0;
    /**
     * Whether the loop is running.
     */
    private bool $running = false;
    /**
     * Resume timer ID.
     */
    private ?string $resumeTimer = null;
    /**
     * Resume deferred ID.
     */
    private ?string $resumeImmediate = null;

    /**
     * Report pause, can be overriden for logging.
     *
     * @param float $timeout Pause duration, 0 = forever
     *
     */
    protected function reportPause(float $timeout): void
    {
    }

    /**
     * Start the loop.
     *
     * Returns false if the loop is already running.
     */
    public function start(): bool
    {
        if ($this->running) {
            return false;
        }
        $this->running = true;
        $this->paused = true;
        \assert($this->resume());
        $this->startedLoop();
        return true;
    }
    /**
     * Stops loop.
     *
     * Returns false if the loop is not running.
     */
    public function stop(): bool
    {
        if (!$this->running) {
            return false;
        }
        $this->running = false;
        if ($this->resumeTimer) {
            $storedWatcherId = $this->resumeTimer;
            EventLoop::cancel($storedWatcherId);
            $this->resumeTimer = null;
        }
        if ($this->resumeImmediate) {
            $storedWatcherId = $this->resumeImmediate;
            EventLoop::cancel($storedWatcherId);
            $this->resumeImmediate = null;
        }
        if ($this->paused) {
            $this->exitedLoop();
        }
        return true;
    }
    abstract protected function loop(): ?float;

    private bool $paused = true;
    private function loopInternal(): void
    {
        $this->paused = false;
        \assert($this->running);
        try {
            $timeout = $this->loop();
        } catch (\Throwable $e) {
            $this->exitedLoopInternal();
            throw $e;
        }
        if (!$this->running) {
            $this->exitedLoopInternal();
            return;
        }
        if ($timeout === self::STOP) {
            $this->exitedLoopInternal();
            return;
        }

        $this->paused = true;
        if ($timeout === self::PAUSE) {
            $this->reportPause(0.0);
        } else {
            if (!$this->resumeImmediate) {
                \assert($this->resumeTimer === null);
                $this->resumeTimer = EventLoop::delay($timeout, function (): void {
                    $this->resumeTimer = null;
                    $this->loopInternal();
                });
            }
            $this->reportPause($timeout);
        }
    }

    private function exitedLoopInternal(): void
    {
        $this->running = false;
        \assert($this->resumeTimer === null);
        if ($this->resumeImmediate) {
            $storedWatcherId = $this->resumeImmediate;
            EventLoop::cancel($storedWatcherId);
            $this->resumeImmediate = null;
        }
        $this->exitedLoop();
    }
    /**
     * Signal that loop has running.
     *
     */
    protected function startedLoop(): void
    {
    }
    /**
     * Signal that loop has exited.
     *
     */
    protected function exitedLoop(): void
    {
    }
    /**
     * Check whether loop is running.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Resume the loop.
     *
     * @return bool Returns false if the loop is not paused.
     */
    public function resume(): bool
    {
        if (!$this->resumeImmediate && $this->running && $this->paused) {
            if ($this->resumeTimer) {
                $timer = $this->resumeTimer;
                $this->resumeTimer = null;
                EventLoop::cancel($timer);
            }
            $this->resumeImmediate = EventLoop::defer(function (): void {
                $this->resumeImmediate = null;
                $this->loopInternal();
            });
            return true;
        }
        return false;
    }
}
