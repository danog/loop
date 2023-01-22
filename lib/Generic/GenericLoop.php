<?php declare(strict_types=1);

/**
 * Generic loop.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Generic;

use Amp\Future;
use danog\Loop\ResumableLoop;

/**
 * Generic loop, runs single callable.
 *
 * @psalm-type TCallableReturn=int|null|Future<int|null>
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
final class GenericLoop extends ResumableLoop
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
     * @var callable():TCallableReturn
     */
    protected $callable;
    private bool $stop = false;
    /**
     * Constructor.
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
     * The loop can be stopped from the outside by using stop().
     *
     * @param callable():TCallableReturn $callable Callable to run
     * @param string   $name     Loop name
     */
    public function __construct(callable $callable, protected string $name)
    {
        $this->callable = $callable;
    }
    /**
     * Loop implementation.
     */
    public function loop(): void
    {
        while (true) {
            $timeout = ($this->callable)();
            if ($this->stop) {
                break;
            }
            if ($timeout instanceof Future) {
                $timeout = $timeout->await();
            }
            if ($timeout === self::PAUSE) {
                $this->reportPause(0);
            } elseif ($timeout > 0) {
                $this->reportPause($timeout);
            }
            if ($timeout === self::STOP || $this->pause($timeout)) {
                break;
            }
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
     * Report pause, can be overriden for logging.
     *
     * @param integer $timeout Pause duration, 0 = forever
     *
     */
    protected function reportPause(int $timeout): void
    {
    }
    /**
     * Get loop name, provided to constructor.
     *
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
