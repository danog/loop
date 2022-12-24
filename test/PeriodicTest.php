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
     *
     *
     * @dataProvider provideTrueFalse
     */
    public function testGeneric(bool $stopSig): void
    {
        $runCount = 0;
        $retValue = false;
        $callable = function () use (&$runCount, &$retValue, &$zis) {
            $zis = $this;
            $runCount++;
            return $retValue;
        };
        $this->fixtureAssertions($callable, $runCount, $retValue, $stopSig, $zis, true);
        $obj = new class() {
            public $retValue = false;
            public $runCount = 0;
            public function run()
            {
                $this->runCount++;
                return $this->retValue;
            }
        };
        $this->fixtureAssertions([$obj, 'run'], $obj->runCount, $obj->retValue, $stopSig, $zisNew, false);
        $obj = new class() {
            public $retValue = false;
            public $runCount = 0;
            public function run()
            {
                $this->runCount++;
                return $this->retValue;
            }
        };
        $this->fixtureAssertions(\Closure::fromCallable([$obj, 'run']), $obj->runCount, $obj->retValue, $stopSig, $zisNew, false);
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
        $retValue = false;
        $callable = function () use (&$runCount, &$retValue, &$zis) {
            $zis = $this;
            delay(0.001);
            $runCount++;
            return $retValue;
        };
        $this->fixtureAssertions($callable, $runCount, $retValue, $stopSig, $zis, true);
        $obj = new class() {
            public $retValue = false;
            public $runCount = 0;
            public function run()
            {
                delay(0.001);
                $this->runCount++;
                return $this->retValue;
            }
        };
        $this->fixtureAssertions([$obj, 'run'], $obj->runCount, $obj->retValue, $stopSig, $zisNew, false);
        $obj = new class() {
            public $retValue = false;
            public $runCount = 0;
            public function run()
            {
                delay(0.001);
                $this->runCount++;
                return $this->retValue;
            }
        };
        $this->fixtureAssertions(\Closure::fromCallable([$obj, 'run']), $obj->runCount, $obj->retValue, $stopSig, $zisNew, false);
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
        $retValue = false;
        $callable = function () use (&$runCount, &$retValue, &$zis): Future {
            $zis = $this;
            $runCount++;
            $f = new DeferredFuture;
            $f->complete($retValue);
            return $f->getFuture();
        };
        $this->fixtureAssertions($callable, $runCount, $retValue, $stopSig, $zis, true);
        $obj = new class() {
            public $retValue = false;
            public $runCount = 0;
            public function run(): Future
            {
                $this->runCount++;
                $f = new DeferredFuture;
                $f->complete($this->retValue);
                return $f->getFuture();
            }
        };
        $this->fixtureAssertions([$obj, 'run'], $obj->runCount, $obj->retValue, $stopSig, $zisNew, false);
        $obj = new class() {
            public $retValue = false;
            public $runCount = 0;
            public function run(): Future
            {
                $this->runCount++;
                $f = new DeferredFuture;
                $f->complete($this->retValue);
                return $f->getFuture();
            }
        };
        $this->fixtureAssertions(\Closure::fromCallable([$obj, 'run']), $obj->runCount, $obj->retValue, $stopSig, $zisNew, false);
    }
    /**
     * Fixture assertions for started loop.
     *
     * @param LoggingInterface $loop Loop
     *
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
     * @param callable $closure  Closure
     * @param integer  $runCount Run count
     * @param bool     $retValue Pause time
     * @param bool     $stopSig  Whether to stop with signal
     * @param bool     $zis      Reference to closure's this
     * @param bool     $checkZis Whether to check zis
     *
     *
     */
    private function fixtureAssertions(callable $closure, int &$runCount, bool &$retValue, bool $stopSig, &$zis, bool $checkZis): void
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
        delay(0.002);
        if ($checkZis) {
            $this->assertEquals($loop, $zis);
        } else {
            $this->assertNull($zis);
        }
        $this->fixtureStarted($loop);

        $this->assertEquals(1, $runCount);

        delay(0.048);
        $this->fixtureStarted($loop);

        $this->assertEquals(1, $runCount);

        delay(0.060);
        $this->fixtureStarted($loop);

        $this->assertEquals(2, $runCount);

        $loop->resume();
        delay(0.002);

        $this->assertEquals(3, $runCount);

        if ($stopSig) {
            $loop->signal(true);
        } else {
            $retValue = true;
            $loop->resume();
        }
        delay(0.002);
        $this->assertEquals($stopSig ? 3 : 4, $runCount);

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
