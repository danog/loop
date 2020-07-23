<?php
/**
 * Loop test trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Traits;

trait Logging
{
    /**
     * Check whether the loop started.
     *
     * @var int
     */
    private $startCounter = 0;
    /**
     * Check whether the loop ended.
     *
     * @var int
     */
    private $endCounter = 0;

    /**
     * Signal that loop started.
     *
     * @return void
     */
    protected function startedLoop(): void
    {
        $this->startCounter++;
        parent::startedLoop();
    }
    /**
     * Signal that loop ended.
     *
     * @return void
     */
    protected function exitedLoop(): void
    {
        $this->endCounter++;
        parent::exitedLoop();
    }

    /**
     * Get start counter.
     *
     * @return integer
     */
    public function startCounter(): int
    {
        return $this->startCounter;
    }
    /**
     * Get end counter.
     *
     * @return integer
     */
    public function endCounter(): int
    {
        return $this->endCounter;
    }
}
