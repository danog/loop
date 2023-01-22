<?php declare(strict_types=1);
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
use danog\Loop\Test\Interfaces\SimpleSignalInterface;
use danog\Loop\Test\Traits\Signal;
use danog\Loop\Test\Traits\SignalSimple;

use function Amp\delay;

class SignalTest extends Fixtures
{
    /**
     * Test signaling loop.
     *
     * @dataProvider provideSignal
     */
    public function testSignal(SignalInterface|SimpleSignalInterface $loop): void
    {
        if ($loop instanceof SignalInterface) {
            $loop->setInterval(500); // Wait 0.5 seconds before returning null
        }

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        $loop->signal(true);
        delay(0.001);
        $this->assertTrue($loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->signal(false);
        delay(0.001);
        $this->assertFalse($loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->signal(null);
        delay(0.001);
        $this->assertNull($loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->signal("test");
        delay(0.001);
        $this->assertEquals("test", $loop->getPayload());
        $this->assertAfterStart($loop);

        $loop->signal($obj = new class {
        });
        delay(0.001);
        $this->assertEquals($obj, $loop->getPayload());
        $this->assertAfterStart($loop);

        if ($loop instanceof SignalInterface) {
            $loop->setInterval(100); // Wait 0.1 seconds before returning null
            $loop->signal(true); // Move along loop to apply new interval
            delay(0.110);
            $this->assertNull($loop->getPayload()); // Result of sleep
        }

        $loop->signal($e = new \RuntimeException('Test'));
        delay(0.001);
        $this->assertEquals($e, $loop->getException());
        $this->assertFinal($loop);
        $loop = null;
    }

    /**
     * Provide resumable loop implementations.
     *
     */
    public function provideSignal(): array
    {
        return [
            [new class() extends SignalLoop implements SimpleSignalInterface {
                use SignalSimple;
            }],
            [new class() extends SignalLoop implements SignalInterface {
                use Signal;
            }],
            [new class() extends ResumableSignalLoop implements SignalInterface {
                use Signal;
            }],
        ];
    }
}
