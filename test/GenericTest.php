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
use danog\Loop\Generic\GenericLoop;
use danog\Loop\Loop;
use danog\Loop\Test\Interfaces\LoggingPauseInterface;
use danog\Loop\Test\Traits\Basic;
use danog\Loop\Test\Traits\LoggingPause;

use function Amp\delay;

class GenericTest extends AsyncTestCase
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
        $pauseTime = GenericLoop::PAUSE;
        $callable = function () use (&$runCount, &$pauseTime) {
            $runCount++;
            return $pauseTime;
        };
        yield from $this->fixtureAssertions($callable, $runCount, $pauseTime, $stopSig);
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
        $pauseTime = GenericLoop::PAUSE;
        $callable = function () use (&$runCount, &$pauseTime): \Generator {
            yield delay(1);
            $runCount++;
            return $pauseTime;
        };
        yield from $this->fixtureAssertions($callable, $runCount, $pauseTime, $stopSig);
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
        $pauseTime = GenericLoop::PAUSE;
        $callable = function () use (&$runCount, &$pauseTime): Promise {
            $runCount++;
            return new Success($pauseTime);
        };
        yield from $this->fixtureAssertions($callable, $runCount, $pauseTime, $stopSig);
    }
    /**
     * Fixture assertions for started loop.
     *
     * @param LoggingPauseInterface $loop Loop
     *
     * @return void
     */
    private function fixtureStarted(LoggingPauseInterface $loop): void
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
     * @param ?integer $pauseTime Pause time
     * @param bool     $stopSig   Whether to stop with signal
     *
     * @return \Generator
     */
    private function fixtureAssertions(\Closure $closure, int &$runCount, ?int &$pauseTime, bool $stopSig = false): \Generator
    {
        $loop = new class($closure, Fixtures::LOOP_NAME) extends GenericLoop implements LoggingPauseInterface {
            use LoggingPause;
        };
        $this->assertEquals(Fixtures::LOOP_NAME, "$loop");

        $this->assertFalse($loop->isRunning());
        $this->assertEquals(0, $loop->startCounter());
        $this->assertEquals(0, $loop->endCounter());

        $this->assertEquals(0, $runCount);
        $this->assertEquals(0, $loop->getPauseCount());

        $loop->start();
        yield delay(2);
        $this->fixtureStarted($loop);

        $this->assertEquals(1, $runCount);
        $this->assertEquals(1, $loop->getPauseCount());
        $this->assertEquals(0, $loop->getLastPause());

        $pauseTime = 100;
        $loop->resume();
        yield delay(2);
        $this->fixtureStarted($loop);

        $this->assertEquals(2, $runCount);
        $this->assertEquals(2, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

        yield delay(48);
        $this->fixtureStarted($loop);

        $this->assertEquals(2, $runCount);
        $this->assertEquals(2, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

        yield delay(60);
        $this->fixtureStarted($loop);

        $this->assertEquals(3, $runCount);
        $this->assertEquals(3, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

        $loop->resume();
        yield delay(1);

        $this->assertEquals(4, $runCount);
        $this->assertEquals(4, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

        if ($stopSig) {
            $loop->signal(true);
        } else {
            $pauseTime = GenericLoop::STOP;
            $loop->resume();
        }
        yield delay(1);
        $this->assertEquals($stopSig ? 4 : 5, $runCount);
        $this->assertEquals(4, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

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
