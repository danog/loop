<?php
/**
 * Exception test trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Traits;

use Generator;

trait BasicException
{
    use Basic;
    /**
     * Loop implementation.
     *
     * @return Generator
     */
    public function loop(): Generator
    {
        $this->inited = true;
        throw new \RuntimeException('Threw exception!');
        $this->ran = true;
    }
}
