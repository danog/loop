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
use Amp\Promise;
use Amp\Success;
use danog\Loop\Generic\PeriodicLoop;
use danog\Loop\Loop;
use danog\Loop\Test\Interfaces\LoggingInterface;
use danog\Loop\Test\Traits\Basic;
use danog\Loop\Test\Traits\Logging;

use function Amp\delay;

class PeriodicTest extends AsyncTestCase
{
    /**
     * Test basic loop.
     *
     * @param bool $stopSig Whether to stop with signal
     *
     * @return \Generator
     *
     * @dataProvider provideTrueFalse
     */
    public function testGeneric(bool $stopSig): \Generator
    {
        $runCount = 0;
        $retValue = false;
        $callable = function () use (&$runCount, &$retValue) {
            $runCount++;
            return $retValue;
        };
        yield from $this->fixtureAssertions($callable, $runCount, $retValue, $stopSig);
    }
    /**
     * Test generator loop.
     *
     * @param bool $stopSig Whether to stop with signal
     *
     * @return \Generator
     *
     * @dataProvider provideTrueFalse
     */
    public function testGenerator(bool $stopSig): \Generator
    {
        $runCount = 0;
        $retValue = false;
        $callable = function () use (&$runCount, &$retValue): \Generator {
            yield delay(1);
            $runCount++;
            return $retValue;
        };
        yield from $this->fixtureAssertions($callable, $runCount, $retValue, $stopSig);
    }
    /**
     * Test promise loop.
     *
     * @param bool $stopSig Whether to stop with signal
     *
     * @return \Generator
     *
     * @dataProvider provideTrueFalse
     */
    public function testPromise(bool $stopSig): \Generator
    {
        $runCount = 0;
        $retValue = false;
        $callable = function () use (&$runCount, &$retValue): Promise {
            $runCount++;
            return new Success($retValue);
        };
        yield from $this->fixtureAssertions($callable, $runCount, $retValue, $stopSig);
    }
    /**
     * Fixture assertions for started loop.
     *
     * @param LoggingInterface $loop Loop
     *
     * @return void
     */
    private function fixtureStarted(LoggingInterface $loop): void
    {
        $this->assertTrue($loop->isRunning());
        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(0, $loop->endCounter());
    }
    /**
     * Run fixture assertions.
     *
     * @param \Closure $closure   Closure
     * @param integer  $runCount  Run count
     * @param bool     $retValue Pause time
     * @param bool     $stopSig   Whether to stop with signal
     *
     * @return \Generator
     */
    private function fixtureAssertions(\Closure $closure, int &$runCount, bool &$retValue, bool $stopSig = false): \Generator
    {
        $loop = new class($closure, Fixtures::LOOP_NAME, 100) extends PeriodicLoop implements LoggingInterface {
            use Logging;
        };
        $this->assertEquals(Fixtures::LOOP_NAME, "$loop");

        $this->assertFalse($loop->isRunning());
        $this->assertEquals(0, $loop->startCounter());
        $this->assertEquals(0, $loop->endCounter());

        $this->assertEquals(0, $runCount);

        $loop->start();
        yield delay(2);
        $this->fixtureStarted($loop);

        $this->assertEquals(1, $runCount);

        yield delay(48);
        $this->fixtureStarted($loop);

        $this->assertEquals(1, $runCount);

        yield delay(60);
        $this->fixtureStarted($loop);

        $this->assertEquals(2, $runCount);

        $loop->resume();
        yield delay(1);

        $this->assertEquals(3, $runCount);

        if ($stopSig) {
            $loop->signal(true);
        } else {
            $retValue = true;
            $loop->resume();
        }
        yield delay(1);
        $this->assertEquals($stopSig ? 3 : 4, $runCount);

        $this->assertFalse($loop->isRunning());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());
    }

    /**
     * Provide true false.
     *
     * @return array
     */
    public function provideTrueFalse(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
