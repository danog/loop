<?php declare(strict_types=1);
/**
 * Resumable test trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Traits;

use danog\Loop\Interfaces\ResumableLoopInterface;

use function Amp\async;
use function Amp\delay;

trait SignalSimple
{
    use Basic;
    /**
     * Signaled payload.
     *
     */
    private $payload;
    /**
     * Signaled exception.
     *
     * @var ?\Throwable
     */
    private $exception;
    /**
     * Get signaled payload.
     *
     */
    public function getPayload()
    {
        return $this->payload;
    }
    /**
     * Get signaled exception.
     */
    public function getException(): ?\Throwable
    {
        return $this->exception;
    }
    /**
     * Loop implementation.
     *
     */
    public function loop(): void
    {
        $this->inited = true;
        try {
            while (true) {
                $this->payload = $this->waitSignal();
            }
        } catch (\Throwable $e) {
            $this->exception = $e;
        }
        $this->ran = true;
    }
}
