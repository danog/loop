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
     * Test waiting signal on interval.
     *
     * @param integer $interval Interval
     *
     * @return \Generator
     */
    private function testGenerator(int $interval): \Generator
    {
        yield delay($interval);
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
                $this->payload = yield $this->waitSignal($this instanceof ResumableLoopInterface ? $this->pause($this->interval) : $this->testGenerator($this->interval));
            }
        } catch (\Throwable $e) {
            $this->exception = $e;
        }
        $this->ran = true;
    }
}
