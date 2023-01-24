<?php declare(strict_types=1);
/**
 * Loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use danog\Loop\Loop;
use danog\Loop\Test\Interfaces\BasicInterface;
use danog\Loop\Test\Traits\Basic;
use danog\Loop\Test\Traits\BasicException;
use Revolt\EventLoop;
use RuntimeException;

use function Amp\delay;

class LoopTest extends Fixtures
{
    /**
     * Execute pre-start assertions.
     */
    protected function assertPreStart(BasicInterface&Loop $loop): void
    {
        $this->assertEquals(self::LOOP_NAME, "$loop");

        $this->assertFalse($loop->isRunning());
        $this->assertFalse($loop->ran());

        $this->assertFalse($loop->inited());

        $this->assertEquals(0, $loop->startCounter());
        $this->assertEquals(0, $loop->endCounter());
    }
    /**
     * Execute after-start assertions.
     *
     * @param bool           $running Whether we should expect the loop to be running
     * @param bool           $running Whether we should actually start the loop by returning control to the event loop
     *
     */
    protected function assertAfterStart(BasicInterface&Loop $loop, bool $running = true, bool $start = true): void
    {
        if ($start) {
            self::waitTick();
        }
        $this->assertTrue($loop->inited());

        if ($running) {
            $this->assertFalse($loop->ran());
        }
        $this->assertEquals($running, $loop->isRunning());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals($running ? 0 : 1, $loop->endCounter());

        $this->assertEquals($running, !$loop->start());
    }
    /**
     * Execute final assertions.
     */
    protected function assertFinal(BasicInterface&Loop $loop): void
    {
        $this->assertTrue($loop->ran());
        $this->assertFalse($loop->isRunning());

        $this->assertTrue($loop->inited());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());
    }
    /**
     * Test basic loop.
     */
    public function testLoop(): void
    {
        $loop = new class() extends Loop implements BasicInterface {
            use Basic;
        };
        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        delay(0.110);

        $this->assertFinal($loop);
    }
    /**
     * Test basic loop.
     */
    public function testLoopStopFromInside(): void
    {
        $loop = new class() extends Loop implements BasicInterface {
            use Basic;
            /**
             * Loop implementation.
             */
            public function loop(): ?float
            {
                $this->inited = true;
                delay(0.1);
                $this->stop();
                $this->ran = true;
                return 1000.0;
            }
        };
        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        delay(0.110);

        $this->assertFinal($loop);
    }
    /**
     * Test basic exception in loop.
     */
    public function testException(): void
    {
        $loop = new class() extends Loop implements BasicInterface {
            use BasicException;
        };

        $e_thrown = null;
        EventLoop::setErrorHandler(function (\RuntimeException $e) use (&$e_thrown): void {
            $e_thrown = $e;
        });

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        self::waitTick();
        $this->assertFalse($loop->isRunning());

        $this->assertTrue($loop->inited());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());

        $this->assertInstanceOf(RuntimeException::class, $e_thrown);
    }
}
