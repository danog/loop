<?php declare(strict_types=1);

/**
 * Loop interface.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Interfaces;

use Stringable;

/**
 * Loop interface.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
interface LoopInterface extends Stringable
{
    /**
     * Start the loop.
     *
     * Returns false if the loop is already running.
     */
    public function start(): bool;
    /**
     * The actual loop function.
     */
    public function loop(): void;
    /**
     * Check whether loop is running.
     */
    public function isRunning(): bool;
}
