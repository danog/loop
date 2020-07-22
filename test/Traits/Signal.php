<?php
/**
 * Resumable test trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Traits;

use danog\Loop\Interfaces\ResumableLoopInterface;
use Generator;

use function Amp\delay;

trait Signal
{
    use Basic;
    use Resumable;
    /**
     * Signaled payload.
     *
     * @var mixed
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
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
    /**
     * Get signaled exception.
     *
     * @return \Throwable
     */
    public function getException(): ?\Throwable
    {
        return $this->exception;
    }
    /**
     * Loop implementation.
     *
     * @return Generator
     */
    public function loop(): Generator
    {
        $this->inited = true;
        try {
            while (true) {
                $this->payload = yield $this->waitSignal($this instanceof ResumableLoopInterface ? $this->pause($this->interval) : delay($this->interval));
            }
        } catch (\Throwable $e) {
            $this->exception = $e;
        }
        $this->ran = true;
    }
}
