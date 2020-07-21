<?php
/**
 * Loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use Amp\PHPUnit\AsyncTestCase;
use danog\Loop\Impl\Loop;
use danog\Loop\Impl\ResumableLoop;
use danog\Loop\Impl\ResumableSignalLoop;
use danog\Loop\Impl\SignalLoop;
use danog\Loop\Test\Traits\Basic;
use danog\Loop\Test\Traits\BasicResumable;

use function Amp\delay;

class LoopTest extends AsyncTestCase
{
    const LOOP_NAME = 'PONY';
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
        $this->assertEquals("$loop", self::LOOP_NAME);

        $this->assertTrue($loop->start());
        $this->assertFalse($loop->start());

        $this->assertTrue($loop->inited());

        $this->assertFalse($loop->ran());
        $this->assertTrue($loop->isRunning());

        $this->assertEquals($loop->startCounter(), 1);
        $this->assertEquals($loop->endCounter(), 0);

        yield delay(110);

        $this->assertTrue($loop->ran());
        $this->assertFalse($loop->isRunning());

        $this->assertEquals($loop->startCounter(), 1);
        $this->assertEquals($loop->endCounter(), 1);
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
            }],


            [new class() extends ResumableLoop implements BasicInterface {
                use BasicResumable;
            }],
            [new class() extends ResumableSignalLoop implements BasicInterface {
                use BasicResumable;
            }],
        ];
    }
}
