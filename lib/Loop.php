<?php declare(strict_types=1);
/**
 * Loop class.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop;

use danog\Loop\Interfaces\LoopInterface;
use danog\Loop\Traits\Loop as TraitsLoop;
use Stringable;

/**
 * Loop abstract class.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
abstract class Loop implements LoopInterface, Stringable
{
    use TraitsLoop;

    private function exitedLoopInternal(): void {
        $this->exitedLoopInternalLoop();
    }
}
