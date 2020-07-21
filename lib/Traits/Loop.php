<?php
/**
 * Loop helper trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Traits;

use Amp\Promise;

use function Amp\asyncCall;

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
     * @return bool
     */
    public function start(): bool
    {
        if ($this->started) {
            return false;
        }
        asyncCall(function (): \Generator {
            $this->startedLoop();
            try {
                yield from $this->loop();
            } finally {
                $this->exitedLoop();
            }
        });
        return true;
    }
    /**
     * Signal that loop has exIited.
     *
     * @return void
     */
    protected function exitedLoop(): void
    {
        $this->started = false;
    }
    /**
     * Signal that loop has started.
     *
     * @return void
     */
    protected function startedLoop(): void
    {
        $this->started = true;
    }
    /**
     * Check whether loop is running.
     *
     * @return boolean
     */
    public function isRunning(): bool
    {
        return $this->started;
    }
}
