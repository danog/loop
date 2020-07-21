<?php

/**
 * Signal loop interface.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Interfaces;

use Amp\Promise;

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
     * @return void
     */
    public function signal($data): void;
    /**
     * Resolve the promise or return|throw the signal.
     *
     * @param Promise|\Generator $promise The original promise or generator
     *
     * @return Promise
     *
     * @template T
     *
     * @psalm-param Promise<T>|\Generator<mixed,Promise|array<array-key,
     *     Promise>,mixed,Promise<T>|T> $promise The original promise or generator
     *
     * @psalm-return Promise<T|mixed>
     */
    public function waitSignal($promise): Promise;
}
