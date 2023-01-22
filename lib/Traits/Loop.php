<?php declare(strict_types=1);
/**
 * Loop helper trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Traits;

use danog\Loop\Interfaces\LoopInterface;
use danog\Loop\Interfaces\ResumableLoopInterface;
use danog\Loop\Interfaces\SignalLoopInterface;
use Revolt\EventLoop;

/**
 * Loop helper trait.
 *
 * Wraps the asynchronous generator methods with asynchronous promise-based methods
 *
 * @author Daniil Gentili <daniil@daniil.it>
 *
 * @psalm-require-implements LoopInterface
 */
trait Loop
{
    /**
     * Whether the loop was started.
     */
    private bool $started = false;
    /**
     * Start the loop.
     *
     * Returns false if the loop is already running.
     */
    public function start(): bool
    {
        if ($this->started) {
            return false;
        }
        EventLoop::queue(function (): void {
            $this->startedLoopInternal();
            try {
                $this->loop();
            } finally {
                $this->exitedLoopInternal();
            }
        });
        return true;
    }
    abstract private function exitedLoopInternal(): void;
    private function exitedLoopInternalLoop(): void
    {
        $this->started = false;
        $this->exitedLoop();
    }
    private function startedLoopInternal(): void
    {
        $this->started = true;
        $this->startedLoop();
    }
    /**
     * Signal that loop has exited.
     *
     */
    protected function exitedLoop(): void
    {
    }
    /**
     * Signal that loop has started.
     *
     */
    protected function startedLoop(): void
    {
    }
    /**
     * Check whether loop is running.
     */
    public function isRunning(): bool
    {
        return $this->started;
    }
}
