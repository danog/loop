<?php declare(strict_types=1);
/**
 * Resumable test trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Traits;

trait Resumable
{
    use Basic;
    /**
     * Set interval.
     */
    protected ?int $interval = 100;
    /**
     * Set sleep interval.
     *
     * @param ?int $interval Interval
     *
     */
    public function setInterval(?int $interval): void
    {
        $this->interval = $interval;
    }
    /**
     * Loop implementation.
     */
    public function loop(): void
    {
        $this->inited = true;
        $this->pause($this->interval);
        $this->ran = true;
    }
}
