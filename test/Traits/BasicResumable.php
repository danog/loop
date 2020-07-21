<?php
/**
 * Resumable test trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Traits;

use Generator;

use function Amp\delay;

trait BasicResumable
{
    use Basic;
    /**
     * Loop implementation.
     *
     * @return Generator
     */
    public function loop(): Generator
    {
        yield $this->pause(0.1);
        $this->ran = true;
    }
}
