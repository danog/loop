<?php
/**
 * Resumable loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use danog\Loop\Interfaces\ResumableLoopInterface;
use danog\Loop\ResumableLoop;
use danog\Loop\ResumableSignalLoop;
use danog\Loop\Test\Interfaces\ResumableInterface;
use danog\Loop\Test\Traits\Resumable;

use function Amp\delay;

class ResumableTest extends Fixtures
{
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
     * Test pausing loop and then resuming it with deferOnce.
     *
     * @param ResumableInterface $loop     Loop
     *
     * @return \Generator
     *
     * @dataProvider provideResumable
     */
    public function testResumableDeferOnce(ResumableInterface $loop): \Generator
    {
        $paused1 = $loop->resumeDeferOnce(); // Will resolve on next pause
        $paused2 = $loop->resumeDeferOnce(); // Will resolve on next pause
        yield delay(1); // Avoid resuming after starting
        $loop->setInterval(10000);

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);


        yield delay(1);
        $this->assertTrue(self::isResolved($paused1)); // Did pause
        $this->assertTrue(self::isResolved($paused2)); // Did pause

        $paused1 = $loop->resumeDeferOnce();
        $paused2 = $loop->resumeDeferOnce();
        $this->assertAfterStart($loop);
        yield delay(1);
        $this->assertFinal($loop);

        yield delay(1);
        $this->assertFalse(self::isResolved($paused1)); // Did not pause again
        $this->assertFalse(self::isResolved($paused2)); // Did not pause again
    }

    /**
     * Provide resumable loop implementations.
     *
     * @return array
     *
     * @psalm-return array<int, array<int, ResumableInterface>>
     */
    public function provideResumable(): array
    {
        return [
            [new class() extends ResumableLoop implements ResumableInterface, ResumableLoopInterface {
                use Resumable;
            }],
            [new class() extends ResumableSignalLoop implements ResumableInterface, ResumableLoopInterface {
                use Resumable;
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
            foreach ([10000, null] as $interval) {
                foreach ($this->provideResumable() as $params) {
                    $params[] = $interval;
                    $params[] = $deferred;
                    yield $params;
                }
            }
        }
    }
}
