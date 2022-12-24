<?php declare(strict_types=1);

/**
 * Loop helper trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Traits;

use Amp\DeferredFuture;
use Amp\Future;

use function Amp\Future\awaitFirst;

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
     * @var ?DeferredFuture
     */
    private ?DeferredFuture $signalDeferred;
    /**
     * Send signal to loop.
     *
     * @param mixed|\Throwable $what Data to signal
     */
    public function signal(mixed $what): void
    {
        if ($this->signalDeferred) {
            $deferred = $this->signalDeferred;
            $this->signalDeferred = null;
            if ($what instanceof \Throwable) {
                $deferred->error($what);
            } else {
                $deferred->complete($what);
            }
        }
    }
    /**
     * @template T
     *
     * @param Future<T> $future
     *
     * @return T|mixed
     */
    public function waitSignal(?Future $future = null): mixed
    {
        $this->signalDeferred = new DeferredFuture();
        if ($future) {
            return awaitFirst([$future, $this->signalDeferred->getFuture()]);
        }
        return $this->signalDeferred->getFuture()->await();
    }
}
