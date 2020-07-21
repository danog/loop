<?php

/**
 * Basic loop test interface.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Interfaces;

use danog\Loop\Interfaces\LoopInterface;

/**
 * Basic loop test interface.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
interface BasicInterface extends LoopInterface
{
    /**
     * Check whether the loop inited.
     *
     * @return boolean
     */
    public function inited(): bool;
    /**
     * Check whether the loop ran.
     *
     * @return boolean
     */
    public function ran(): bool;
    /**
     * Get start counter.
     *
     * @return integer
     */
    public function startCounter(): int;
    /**
     * Get end counter.
     *
     * @return integer
     */
    public function endCounter(): int;
}
