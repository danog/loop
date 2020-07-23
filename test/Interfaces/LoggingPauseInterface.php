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
interface LoggingPauseInterface extends LoopInterface, LoggingInterface
{
    /**
     * Get number of times loop was paused.
     *
     * @return integer
     */
    public function getPauseCount(): int;
    /**
     * Get last pause.
     *
     * @return integer
     */
    public function getLastPause(): int;
}
