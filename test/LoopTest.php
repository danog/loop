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

use function Amp\delay;

class LoopTest extends Fixtures
{
    /**
     * Test basic loop.
     *
     * @dataProvider provideBasic
     */
    public function testLoop(Loop&BasicInterface $loop): void
    {
        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        delay(0.110);

        $this->assertFinal($loop);
    }
    /**
     * Test basic exception in loop.
     *
     * @dataProvider provideBasicExceptions
     */
    public function testException(Loop&BasicInterface $loop): void
    {
        $this->markTestSkipped();

        $this->expectException(\RuntimeException::class);

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        delay(0.001);
        $this->assertFalse($loop->isRunning());

        $this->assertTrue($loop->inited());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());
    }

    /**
     * Provide loop implementations.
     *
     */
    public function provideBasic(): array
    {
        return [
            [new class() extends Loop implements BasicInterface {
                use Basic;
            }],
        ];
    }
    /**
     * Provide loop implementations.
     *
     */
    public function provideBasicExceptions(): array
    {
        return [
            [new class() extends Loop implements BasicInterface {
                use BasicException;
            }],
        ];
    }
}
