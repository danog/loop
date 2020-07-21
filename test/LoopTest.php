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
        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        yield delay(110);

        $this->assertFinal($loop);
    }
    /**
     * Test pausing loop.
     *
     * @param ResumableInterface $loop Loop
     *
     * @return \Generator
     *
     * @dataProvider provideResumable
     */
    public function testResumable(ResumableInterface $loop): \Generator
    {
        $paused = $loop->resume(); // Returned promise will resolve on next pause

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        yield delay(10);
        $this->assertTrue(self::isResolved($paused));
        yield delay(100);

        $this->assertFinal($loop);
    }
    /**
     * Test pausing loop with negative value.
     *
     * @param ResumableInterface $loop Loop
     *
     * @return \Generator
     *
     * @dataProvider provideResumable
     */
    public function testResumableNegative(ResumableInterface $loop)
    {
        $paused = $loop->resume(); // Will resolve on next pause
        $loop->setInterval(-1); // Will not pause, and finish right away!

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());

        yield delay(1);
        $this->assertFalse(self::isResolved($paused)); // Did not pause

        // Invert the order as the afterTest assertions will begin the test anew
        $this->assertFinal($loop);
        $this->assertAfterStart($loop, false);
    }
    /**
     * Test pausing loop forever, or for 10 seconds, prematurely resuming it.
     *
     * @param ResumableInterface $loop     Loop
     * @param ?int               $interval Interval
     * @param bool               $deferred Deferred
     *
     * @return \Generator
     *
     * @dataProvider provideResumableInterval
     */
    public function testResumableForeverPremature(ResumableInterface $loop, ?int $interval, bool $deferred): \Generator
    {
        $paused = $deferred ? $loop->resumeDefer() : $loop->resume(); // Will resolve on next pause
        if ($deferred) {
            yield delay(1); // Avoid resuming after starting
        }
        $loop->setInterval($interval);

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);


        yield delay(1);
        $this->assertTrue(self::isResolved($paused)); // Did pause

        $paused = $deferred ? $loop->resumeDefer() : $loop->resume();
        if ($deferred) {
            $this->assertAfterStart($loop);
            yield delay(1);
        }
        $this->assertFinal($loop);

        yield delay(1);
        $this->assertFalse(self::isResolved($paused)); // Did not pause again
    }

    /**
     * Check if promise has been resolved afterwards.
     *
     * @param Promise $promise Promise
     *
     * @return boolean
     */
    public static function isResolved(Promise $promise): bool
    {
        $resolved = false;
        $promise->onResolve(static function ($e, $res) use (&$resolved) {
            if ($e) {
                throw $e;
            }
            $resolved = true;
        });
        return $resolved;
    }
    /**
     * Execute pre-start assertions.
     *
     * @param BasicInterface $loop Loop
     *
     * @return void
     */
    public function assertPreStart(BasicInterface $loop)
    {
        $this->assertEquals(self::LOOP_NAME, "$loop");

        $this->assertFalse($loop->isRunning());
        $this->assertFalse($loop->ran());

        $this->assertFalse($loop->inited());

        $this->assertEquals(0, $loop->startCounter());
        $this->assertEquals(0, $loop->endCounter());
    }
    /**
     * Execute after-start assertions.
     *
     * @param BasicInterface $loop    Loop
     * @param bool           $running Whether we should expect the loop to be running
     *
     * @return void
     */
    public function assertAfterStart(BasicInterface $loop, bool $running = true)
    {
        $this->assertTrue($loop->inited());

        if ($running) {
            $this->assertFalse($loop->ran());
        }
        $this->assertEquals($running, $loop->isRunning(), $running);

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals($running ? 0 : 1, $loop->endCounter());

        $this->assertEquals($running, !$loop->start());
    }
    /**
     * Execute final assertions.
     *
     * @param BasicInterface $loop Loop
     *
     * @return void
     */
    public function assertFinal(BasicInterface $loop)
    {
        $this->assertTrue($loop->ran());
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
     * Provide resumable loop implementations.
     *
     * @return array
     */
    public function provideResumable(): array
    {
        return [
            [new class() extends ResumableLoop implements ResumableInterface {
                use BasicResumable;
            }],
            [new class() extends ResumableSignalLoop implements ResumableInterface {
                use BasicResumable;
            }],
        ];
    }
    /**
     * Provide resumable loop implementations and interval.
     *
     * @return \Generator
     */
    public function provideResumableInterval(): \Generator
    {
        foreach ([true, false] as $deferred) {
            foreach ([10, null] as $interval) {
                foreach ($this->provideResumable() as $params) {
                    $params[] = $interval;
                    $params[] = $deferred;
                    yield $params;
                }
            }
        }
    }
}
