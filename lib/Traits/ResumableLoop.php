<?php declare(strict_types=1);

/**
 * Loop helper trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Traits;

use Amp\Deferred;
use Amp\DeferredFuture;
use Amp\Future;
use Revolt\EventLoop;

/**
 * Resumable loop helper trait.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
trait ResumableLoop
{
    use Loop {
        exitedLoop as private parentExitedLoop;
    }
    /**
     * Resume deferred.
     *
     * @var ?DeferredFuture<null>
     */
    private ?DeferredFuture $resume = null;
    /**
     * Pause deferred.
     *
     * @var ?DeferredFuture<null>
     */
    private ?DeferredFuture $pause = null;
    /**
     * Resume timer ID.
     */
    private ?string $resumeTimer = null;
    /**
     * Resume deferred ID.
     */
    private ?string $resumeDeferred = null;
    /**
     * Pause the loop.
     *
     * @param ?int $time For how long to pause the loop, if null will pause forever (until resume is called from outside of the loop)
     */
    public function pause(?int $time = null): void
    {
        if (!\is_null($time)) {
            if ($time <= 0) {
                return;
            }
            if ($this->resumeTimer) {
                EventLoop::cancel($this->resumeTimer);
                $this->resumeTimer = null;
            }
            /** @psalm-suppress MixedArgumentTypeCoercion */
            $this->resumeTimer = EventLoop::delay($time/1000, $this->resumeInternal(...));
        }

        $pause = $this->pause;
        $this->pause = new DeferredFuture();
        if ($pause) {
            $pause = $pause->getFuture();
            /**
             * @psalm-suppress InvalidArgument
             */
            EventLoop::defer($pause->complete(...));
        }

        $this->resume = new DeferredFuture();
        $this->resume->getFuture()->await();
    }
    /**
     * Resume the loop.
     *
     * @return Future<null> Resolved when the loop is paused again
     */
    public function resume(): Future
    {
        if (!$this->pause) {
            $this->pause = new DeferredFuture;
        }
        $promise = $this->pause->getFuture();
        $this->resumeInternal();
        return $promise;
    }
    /**
     * Defer resuming the loop to next tick.
     *
     * @return Future<null> Resolved when the loop is paused again
     */
    public function resumeDefer(): Future
    {
        EventLoop::defer($this->resumeInternal(...));
        if (!$this->pause) {
            $this->pause = new DeferredFuture;
        }
        return $this->pause->getFuture();
    }
    /**
     * Defer resuming the loop to next tick.
     *
     * Multiple consecutive calls will only one resume.
     *
     * @return Future<null> Resolved when the loop is paused again
     */
    public function resumeDeferOnce(): Future
    {
        if (!$this->resumeDeferred) {
            $this->resumeDeferred = EventLoop::defer(function (): void {
                $this->resumeDeferred = null;
                $this->resumeInternal();
            });
        }
        if (!$this->pause) {
            $this->pause = new DeferredFuture;
        }
        return $this->pause->getFuture();
    }
    /**
     * Internal resume function.
     */
    private function resumeInternal(): void
    {
        if ($this->resumeTimer) {
            $storedWatcherId = $this->resumeTimer;
            EventLoop::cancel($storedWatcherId);
            $this->resumeTimer = null;
        }
        if ($this->resume) {
            $resume = $this->resume;
            $this->resume = null;
            $resume->complete();
        }
    }

    /**
     * Signal that loop has exIited.
     *
     */
    protected function exitedLoop(): void
    {
        $this->parentExitedLoop();
        if ($this->resumeTimer) {
            EventLoop::cancel($this->resumeTimer);
            $this->resumeTimer = null;
        }
    }
}
