<?php
/**
 * Signal loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use Amp\Loop;
use danog\Loop\ResumableSignalLoop;
use danog\Loop\SignalLoop;
use danog\Loop\Test\Interfaces\SignalInterface;
use danog\Loop\Test\Traits\Signal;

use function Amp\delay;

class SignalTest extends Fixtures
{
    /**
     * Test signaling loop.
     *
     * @param SignalInterface $loop Loop
     *
     * @return \Generator
     *
     * @dataProvider provideSignal
     */
    public function testSignal(SignalInterface $loop): \Generator
    {
        $loop->setInterval(500); // Wait 0.5 seconds before returning null

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        $loop->signal(true);
        $this->assertTrue($loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->signal(false);
        $this->assertFalse($loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->signal(null);
        $this->assertNull($loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->signal("test");
        $this->assertEquals("test", $loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->signal($obj = new class {
        });
        $this->assertEquals($obj, $loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->setInterval(100); // Wait 0.1 seconds before returning null
        $loop->signal(true); // Move along loop to apply new interval
        yield delay(110);
        $this->assertNull($loop->getPayload()); // Result of sleep

        $loop->signal($e = new \RuntimeException('Test'));
        $this->assertEquals($e, $loop->getException());
        $this->assertFinal($loop);
        $loop = null;
    }

    /**
     * Provide resumable loop implementations.
     *
     * @return array
     */
    public function provideSignal(): array
    {
        return [
            [new class() extends SignalLoop implements SignalInterface {
                use Signal;
            }],
            [new class() extends ResumableSignalLoop implements SignalInterface {
                use Signal;
            }],
        ];
    }
}
