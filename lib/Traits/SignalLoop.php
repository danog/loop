<?php

/**
 * Loop helper trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Traits;

use Amp\Coroutine;
use Amp\Deferred;
use Amp\Promise;

/**
 * Signal loop helper trait.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
trait SignalLoop
{
    /**
     * Signal deferred.
     *
     * @var ?Deferred
     */
    private $signalDeferred;
    /**
     * Send signal to loop.
     *
     * @param mixed|\Throwable $what Data to signal
     *
     * @return void
     */
    public function signal($what): void
    {
        if ($this->signalDeferred) {
            $deferred = $this->signalDeferred;
            $this->signalDeferred = null;
            if ($what instanceof \Throwable) {
                $deferred->fail($what);
            } else {
                $deferred->resolve($what);
            }
        }
    }
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
    public function waitSignal($promise): Promise
    {
        if ($promise instanceof \Generator) {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            $promise = new Coroutine($promise);
        }
        $this->signalDeferred = new Deferred();
        $combinedPromise = $this->signalDeferred->promise();
        $promise->onResolve(
            function () use ($promise) {
                if ($this->signalDeferred !== null) {
                    $deferred = $this->signalDeferred;
                    $this->signalDeferred = null;
                    $deferred->resolve($promise);
                }
            }
        );
        return $combinedPromise;
    }
}
