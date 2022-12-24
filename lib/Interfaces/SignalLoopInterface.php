<?php declare(strict_types=1);

/**
 * Signal loop interface.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Interfaces;

use Amp\Future;

/**
 * Signal loop interface.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
interface SignalLoopInterface extends LoopInterface
{
    /**
     * Send a signal to the the loop.
     *
     * @param \Throwable|mixed $data Signal to send
     *
     */
    public function signal($data): void;
    /**
     * @template T
     *
     * @param Future<T> $future
     *
     * @return T|mixed
     */
    public function waitSignal(?Future $future = null): mixed;
}
