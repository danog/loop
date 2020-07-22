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

trait Resumable
{
    use Basic;
    /**
     * Set interval.
     *
     * @var ?int
     */
    protected $interval = 100;
    /**
     * Set sleep interval.
     *
     * @param ?int $interval Interval
     *
     * @return void
     */
    public function setInterval(?int $interval): void
    {
        $this->interval = $interval;
    }
    /**
     * Loop implementation.
     *
     * @return Generator
     */
    public function loop(): Generator
    {
        $this->inited = true;
        yield $this->pause($this->interval);
        $this->ran = true;
    }
}
