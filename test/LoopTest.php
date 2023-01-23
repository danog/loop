<?php declare(strict_types=1);
/**
 * Loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use Amp\PHPUnit\UnhandledException;
use danog\Loop\Loop;
use danog\Loop\Test\Interfaces\BasicInterface;
use danog\Loop\Test\Traits\Basic;
use danog\Loop\Test\Traits\BasicException;

use function Amp\delay;

class LoopTest extends Fixtures
{
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
                return 10.0;
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
        $this->expectException(UnhandledException::class);

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        delay(0.001);
        $this->assertFalse($loop->isRunning());

        $this->assertTrue($loop->inited());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());
    }
}
