<?php
/**
 * Signal loop class.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop;

use danog\Loop\Interfaces\SignalLoopInterface;
use danog\Loop\Traits\Loop;
use danog\Loop\Traits\SignalLoop as TraitsSignalLoop;

/**
 * Signal loop abstract class.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
abstract class SignalLoop implements SignalLoopInterface
{
    use Loop;
    use TraitsSignalLoop;
}
