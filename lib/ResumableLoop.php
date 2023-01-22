<?php declare(strict_types=1);
/**
 * Resumable loop class.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop;

use danog\Loop\Interfaces\ResumableLoopInterface;
use danog\Loop\Traits\Loop;
use danog\Loop\Traits\ResumableLoop as TraitsResumableLoop;

/**
 * Resumable loop abstract class.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
abstract class ResumableLoop implements ResumableLoopInterface
{
    use Loop;
    use TraitsResumableLoop;

    private function exitedLoopInternal(): void
    {
        $this->exitedLoopInternalLoop();
        $this->exitedLoopInternalResumable();
    }
}
