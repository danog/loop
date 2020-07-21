<?php

/**
 * Loop interface.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Interfaces;

/**
 * Loop interface.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
interface LoopInterface
{
    /**
     * Start the loop.
     *
     * Returns false if the loop is already running.
     *
     * @return bool
     */
    public function start(): bool;
    /**
     * The actual loop function.
     *
     * @return \Generator
     */
    public function loop(): \Generator;
    /**
     * Get name of the loop.
     *
     * @return string
     */
    public function __toString(): string;
    /**
     * Check whether loop is running.
     *
     * @return boolean
     */
    public function isRunning(): bool;
}
