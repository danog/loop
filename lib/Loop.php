<?php declare(strict_types=1);
/**
 * Loop class.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop;

use danog\Loop\Interfaces\LoopInterface;
use Revolt\EventLoop;

/**
 * Loop abstract class.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
abstract class Loop implements LoopInterface
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
            $this->startedLoop();
            try {
                $this->loop();
            } finally {
                $this->exitedLoop();
            }
        });
        return true;
    }

    /**
     * Signal that loop has started.
     *
     */
    protected function startedLoop(): void
    {
        $this->started = true;
    }
    /**
     * Signal that loop has exited.
     *
     */
    protected function exitedLoop(): void
    {
        $this->started = false;
    }
    /**
     * Check whether loop is running.
     */
    public function isRunning(): bool
    {
        return $this->started;
    }
}
