<?php declare(strict_types=1);
/**
 * Periodic loop.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Generic;

use Amp\Future;
use danog\Loop\ResumableLoop;

/**
 * Periodic loop.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
final class PeriodicLoop extends ResumableLoop
{
    /**
     * Callback.
     *
     * @var callable
     *
     * @psalm-var callable():(bool|Future<bool>)
     */
    private $callback;
    private bool $stop = false;
    /**
     * Constructor.
     *
     *
     * Runs a callback at a periodic interval.
     *
     * The loop can be stopped from the outside by calling stop()
     * and from the inside by returning `true`.
     *
     * @param callable():(bool|Future<bool>) $callback Callable to run
     * @param string   $name     Loop name
     * @param ?int     $interval Loop interval
     *
     */
    public function __construct(callable $callback, private string $name, private ?int $interval)
    {
        $this->callback = $callback;
        $this->name = $name;
        $this->interval = $interval;
    }
    /**
     * Loop implementation.
     */
    public function loop(): void
    {
        while (true) {
            $result = ($this->callback)();
            if ($result instanceof Future) {
                $result = $result->await();
            }
            if ($result === true || $this->stop) {
                break;
            }
            $this->pause($this->interval);
            if ($this->stop) {
                break;
            }
        }
    }
    /**
     * Stops loop.
     */
    public function stop(): void
    {
        $this->stop = true;
        $this->resume();
    }
    protected function startedLoop(): void
    {
        parent::startedLoop();
        $this->stop = false;
    }
    /**
     * Get name of the loop, passed to the constructor.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
