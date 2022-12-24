<?php declare(strict_types=1);

/**
 * Signal loop test interface.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Interfaces;

use danog\Loop\Interfaces\SignalLoopInterface;

/**
 * Signal loop test interface.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
interface SignalInterface extends BasicInterface, IntervalInterface, SignalLoopInterface
{
    /**
     * Get signaled payload.
     *
     */
    public function getPayload();
    /**
     * Get signaled exception.
     */
    public function getException(): ?\Throwable;
}
