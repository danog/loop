<?php
/**
 * Loop test trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Traits;

use danog\Loop\Test\LoopTest;
use Generator;

use function Amp\delay;

trait Basic
{
    use Logging;
    /**
     * Check whether the loop inited.
     *
     * @var bool
     */
    private $inited = false;
    /**
     * Check whether the loop ran.
     *
     * @var bool
     */
    private $ran = false;
    /**
     * Check whether the loop inited.
     *
     * @return boolean
     */
    public function inited(): bool
    {
        return $this->inited;
    }
    /**
     * Check whether the loop ran.
     *
     * @return boolean
     */
    public function ran(): bool
    {
        return $this->ran;
    }
    /**
     * Loop implementation.
     *
     * @return Generator
     */
    public function loop(): Generator
    {
        $this->inited = true;
        yield delay(100);
        $this->ran = true;
    }
    /**
     * Get loop name.
     *
     * @return string
     */
    public function __toString(): string
    {
        return LoopTest::LOOP_NAME;
    }
}
