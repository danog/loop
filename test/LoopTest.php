<?php
/**
 * Loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use danog\Loop\Loop;
use danog\Loop\ResumableLoop;
use danog\Loop\ResumableSignalLoop;
use danog\Loop\SignalLoop;
use danog\Loop\Test\Interfaces\BasicInterface;
use danog\Loop\Test\Traits\Basic;
use danog\Loop\Test\Traits\BasicException;

use function Amp\delay;

class LoopTest extends Fixtures
{
    /**
     * Test basic loop.
     *
     * @param BasicInterface $loop Loop
     *
     * @return \Generator
     *
     * @dataProvider provideBasic
     */
    public function testLoop(BasicInterface $loop): \Generator
    {
        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        yield delay(110);

        $this->assertFinal($loop);
    }
    /**
     * Test basic exception in loop.
     *
     * @param BasicInterface $loop Loop
     *
     * @return void
     *
     * @dataProvider provideBasicExceptions
     */
    public function testException(BasicInterface $loop): void
    {
        $this->expectException(\RuntimeException::class);

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertFalse($loop->isRunning());

        $this->assertTrue($loop->inited());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());
    }

    /**
     * Provide loop implementations.
     *
     * @return array
     */
    public function provideBasic(): array
    {
        return [
            [new class() extends Loop implements BasicInterface {
                use Basic;
            }],
            [new class() extends SignalLoop implements BasicInterface {
                use Basic;
            }],
            [new class() extends ResumableLoop implements BasicInterface {
                use Basic;
            }],
            [new class() extends ResumableSignalLoop implements BasicInterface {
                use Basic;
            }]
        ];
    }
    /**
     * Provide loop implementations.
     *
     * @return array
     */
    public function provideBasicExceptions(): array
    {
        return [
            [new class() extends Loop implements BasicInterface {
                use BasicException;
            }],
            [new class() extends SignalLoop implements BasicInterface {
                use BasicException;
            }],
            [new class() extends ResumableLoop implements BasicInterface {
                use BasicException;
            }],
            [new class() extends ResumableSignalLoop implements BasicInterface {
                use BasicException;
            }]
        ];
    }
}
