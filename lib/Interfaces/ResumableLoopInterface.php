<?php

/**
 * Resumable loop interface.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Interfaces;

use Amp\Promise;

/**
 * Resumable loop interface.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
interface ResumableLoopInterface extends LoopInterface
{
    /**
     * Pause the loop.
     *
     * @param ?int $time Milliseconds for how long to pause the loop, if null will pause forever (until resume is called from outside of the loop)
     *
     * @return Promise Resolved when the loop is resumed
     */
    public function pause(?int $time = null): Promise;
    /**
     * Resume the loop.
     *
     * @return Promise Resolved when the loop is paused again
     */
    public function resume(): Promise;
    /**
     * Defer resuming the loop to next tick.
     *
     * @return Promise Resolved when the loop is paused again
     */
    public function resumeDefer(): Promise;
    /**
     * Defer resuming the loop to next tick.
     *
     * Multiple consecutive calls will yield only one resume.
     *
     * @return Promise Resolved when the loop is paused again
     */
    public function resumeDeferOnce(): Promise;
}
