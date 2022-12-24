<?php declare(strict_types=1);
/**
 * Loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use Amp\DeferredFuture;
use Amp\Future;
use Amp\PHPUnit\AsyncTestCase;
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
     *
     *
     * @dataProvider provideTrueFalse
     */
    public function testGeneric(bool $stopSig): void
    {
        $runCount = 0;
        $pauseTime = GenericLoop::PAUSE;
        $callable = function () use (&$runCount, &$pauseTime, &$zis) {
            $zis = $this;
            $runCount++;
            return $pauseTime;
        };
        $this->fixtureAssertions($callable, $runCount, $pauseTime, $stopSig, $zis, true);
        $obj = new class() {
            public $pauseTime = GenericLoop::PAUSE;
            public $runCount = 0;
            public function run()
            {
                $this->runCount++;
                return $this->pauseTime;
            }
        };
        $this->fixtureAssertions([$obj, 'run'], $obj->runCount, $obj->pauseTime, $stopSig, $zisNew, false);
        $obj = new class() {
            public $pauseTime = GenericLoop::PAUSE;
            public $runCount = 0;
            public function run()
            {
                $this->runCount++;
                return $this->pauseTime;
            }
        };
        $this->fixtureAssertions(\Closure::fromCallable([$obj, 'run']), $obj->runCount, $obj->pauseTime, $stopSig, $zisNew, false);
    }
    /**
     * Test generator loop.
     *
     * @param bool $stopSig Whether to stop with signal
     *
     *
     *
     * @dataProvider provideTrueFalse
     */
    public function testGenerator(bool $stopSig): void
    {
        $runCount = 0;
        $pauseTime = GenericLoop::PAUSE;
        $callable = function () use (&$runCount, &$pauseTime, &$zis) {
            $zis = $this;
            delay(0.001);
            $runCount++;
            return $pauseTime;
        };
        $this->fixtureAssertions($callable, $runCount, $pauseTime, $stopSig, $zis, true);
        $obj = new class() {
            public $pauseTime = GenericLoop::PAUSE;
            public $runCount = 0;
            public function run()
            {
                delay(0.001);
                $this->runCount++;
                return $this->pauseTime;
            }
        };
        $this->fixtureAssertions([$obj, 'run'], $obj->runCount, $obj->pauseTime, $stopSig, $zisNew, false);
        $obj = new class() {
            public $pauseTime = GenericLoop::PAUSE;
            public $runCount = 0;
            public function run()
            {
                delay(0.001);
                $this->runCount++;
                return $this->pauseTime;
            }
        };
        $this->fixtureAssertions(\Closure::fromCallable([$obj, 'run']), $obj->runCount, $obj->pauseTime, $stopSig, $zisNew, false);
    }
    /**
     * Test promise loop.
     *
     * @param bool $stopSig Whether to stop with signal
     *
     *
     *
     * @dataProvider provideTrueFalse
     */
    public function testPromise(bool $stopSig): void
    {
        $runCount = 0;
        $pauseTime = GenericLoop::PAUSE;
        $callable = function () use (&$runCount, &$pauseTime, &$zis): Future {
            $zis = $this;
            $runCount++;
            $f = new DeferredFuture;
            $f->complete($pauseTime);
            return $f->getFuture();
        };
        $this->fixtureAssertions($callable, $runCount, $pauseTime, $stopSig, $zis, true);
        $obj = new class() {
            public $pauseTime = GenericLoop::PAUSE;
            public $runCount = 0;
            public function run(): Future
            {
                $this->runCount++;
                $f = new DeferredFuture;
                $f->complete($this->pauseTime);
                return $f->getFuture();
            }
        };
        $this->fixtureAssertions([$obj, 'run'], $obj->runCount, $obj->pauseTime, $stopSig, $zisNew, false);
        $obj = new class() {
            public $pauseTime = GenericLoop::PAUSE;
            public $runCount = 0;
            public function run(): Future
            {
                $this->runCount++;
                $f = new DeferredFuture;
                $f->complete($this->pauseTime);
                return $f->getFuture();
            }
        };
        $this->fixtureAssertions(\Closure::fromCallable([$obj, 'run']), $obj->runCount, $obj->pauseTime, $stopSig, $zisNew, false);
    }
    /**
     * Fixture assertions for started loop.
     *
     * @param LoggingPauseInterface $loop Loop
     *
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
     * @param callable $closure   Closure
     * @param integer  $runCount  Run count
     * @param ?integer $pauseTime Pause time
     * @param bool     $stopSig   Whether to stop with signal
     * @param bool     $zis       Reference to closure's this
     * @param bool     $checkZis  Whether to check zis
     *
     *
     */
    private function fixtureAssertions(callable $closure, int &$runCount, ?int &$pauseTime, bool $stopSig, &$zis, bool $checkZis): void
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
        delay(0.002);
        if ($checkZis) {
            $this->assertEquals($loop, $zis);
        } else {
            $this->assertNull($zis);
        }
        $this->fixtureStarted($loop);

        $this->assertEquals(1, $runCount);
        $this->assertEquals(1, $loop->getPauseCount());
        $this->assertEquals(0, $loop->getLastPause());

        $pauseTime = 100;
        $loop->resume();
        delay(0.002);
        $this->fixtureStarted($loop);

        $this->assertEquals(2, $runCount);
        $this->assertEquals(2, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

        delay(0.048);
        $this->fixtureStarted($loop);

        $this->assertEquals(2, $runCount);
        $this->assertEquals(2, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

        delay(0.060);
        $this->fixtureStarted($loop);

        $this->assertEquals(3, $runCount);
        $this->assertEquals(3, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

        $loop->resume();
        delay(0.002);

        $this->assertEquals(4, $runCount);
        $this->assertEquals(4, $loop->getPauseCount());
        $this->assertEquals(100, $loop->getLastPause());

        if ($stopSig) {
            $loop->signal(true);
        } else {
            $pauseTime = GenericLoop::STOP;
            $loop->resume();
        }
        delay(0.002);
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
     */
    public function provideTrueFalse(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
