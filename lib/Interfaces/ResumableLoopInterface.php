<?php declare(strict_types=1);

/**
 * Resumable loop interface.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Interfaces;

use Amp\Future;

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
     */
    public function pause(?int $time = null): void;
    /**
     * Resume the loop.
     */
    public function resume(): Future;
    /**
     * Defer resuming the loop to next tick.
     *
     * @return Promise Resolved when the loop is paused again
     */
    public function resumeDefer(): Future;
    /**
     * Defer resuming the loop to next tick.
     *
     * Multiple consecutive calls will only one resume.
     *
     * @return Promise Resolved when the loop is paused again
     */
    public function resumeDeferOnce(): Future;
}
