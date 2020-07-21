<?php
/**
 * Resumable loop class.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop;

use danog\Loop\Interfaces\ResumableLoopInterface;
use danog\Loop\Traits\ResumableLoop as TraitsResumableLoop;

/**
 * Resumable loop abstract class.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
abstract class ResumableLoop implements ResumableLoopInterface
{
    use TraitsResumableLoop;
}
