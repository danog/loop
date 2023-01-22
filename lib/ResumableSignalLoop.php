<?php declare(strict_types=1);
/**
 * Resumable signal loop class.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop;

use danog\Loop\Interfaces\ResumableLoopInterface;
use danog\Loop\Interfaces\SignalLoopInterface;
use danog\Loop\Traits\Loop;
use danog\Loop\Traits\ResumableLoop;
use danog\Loop\Traits\SignalLoop;

/**
 * Resumable signal loop abstract class.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
abstract class ResumableSignalLoop implements ResumableLoopInterface, SignalLoopInterface
{
    use Loop;
    use ResumableLoop;
    use SignalLoop;

    private function exitedLoopInternal(): void
    {
        $this->exitedLoopInternalLoop();
        $this->exitedLoopInternalResumable();
        $this->exitedLoopInternalSignal();
    }
}
