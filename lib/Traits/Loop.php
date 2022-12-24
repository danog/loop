<?php declare(strict_types=1);
/**
 * Loop helper trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Traits;

use function Amp\async;

/**
 * Loop helper trait.
 *
 * Wraps the asynchronous generator methods with asynchronous promise-based methods
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
trait Loop
{
    /**
     * Whether the loop was started.
     *
     * @var bool
     */
    private $started = false;
    /**
     * Start the loop.
     *
     * Returns false if the loop is already running.
     *
     */
    public function start(): bool
    {
        if ($this->started) {
            return false;
        }
        async(function (): void {
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
     * Signal that loop has exIited.
     *
     */
    protected function exitedLoop(): void
    {
        $this->started = false;
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
     * Check whether loop is running.
     */
    public function isRunning(): bool
    {
        return $this->started;
    }
}
