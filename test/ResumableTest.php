<?php declare(strict_types=1);
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
use Generator;

use function Amp\delay;

class ResumableTest extends Fixtures
{
    /**
     * Test pausing loop.
     *
     * @param ResumableInterface $loop Loop
     *
     *
     *
     * @dataProvider provideResumable
     */
    public function testResumable(ResumableInterface $loop): void
    {
        $paused = $loop->resume(); // Returned promise will resolve on next pause

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        delay(0.010);
        $this->assertTrue($paused->isComplete());
        delay(0.1);

        $this->assertFinal($loop);
    }
    /**
     * Test pausing loop with negative value.
     *
     * @param ResumableInterface $loop Loop
     *
     *
     *
     * @dataProvider provideResumable
     */
    public function testResumableNegative(ResumableInterface $loop): void
    {
        $paused = $loop->resume(); // Will resolve on next pause
        $loop->setInterval(-1); // Will not pause, and finish right away!

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());

        delay(0.001);
        $this->assertFalse($paused->isComplete()); // Did not pause

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
     *
     *
     * @dataProvider provideResumableInterval
     */
    public function testResumableForeverPremature(ResumableInterface $loop, ?int $interval, bool $deferred): void
    {
        $paused = $deferred ? $loop->resumeDefer() : $loop->resume(); // Will resolve on next pause
        if ($deferred) {
            delay(0.001); // Avoid resuming after starting
        }
        $loop->setInterval($interval);

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        delay(0.001);
        $this->assertTrue($paused->isComplete()); // Did pause

        $paused = $deferred ? $loop->resumeDefer() : $loop->resume();
        if ($deferred) {
            $this->assertAfterStart($loop, true, false);
        }
        delay(0.001);
        $this->assertFinal($loop);

        delay(0.001);
        $this->assertFalse($paused->isComplete()); // Did not pause again
    }

    /**
     * Test pausing loop and then resuming it with deferOnce.
     *
     * @param ResumableInterface $loop     Loop
     *
     *
     *
     * @dataProvider provideResumable
     */
    public function testResumableDeferOnce(ResumableInterface $loop): void
    {
        $paused1 = $loop->resumeDeferOnce(); // Will resolve on next pause
        $paused2 = $loop->resumeDeferOnce(); // Will resolve on next pause
        delay(0.001); // Avoid resuming after starting
        $loop->setInterval(10000);

        $this->assertPreStart($loop);
        $this->assertTrue($loop->start());
        $this->assertAfterStart($loop);

        delay(0.001);
        $this->assertTrue($paused1->isComplete()); // Did pause
        $this->assertTrue($paused2->isComplete()); // Did pause

        $paused1 = $loop->resumeDeferOnce();
        $paused2 = $loop->resumeDeferOnce();

        $this->assertFalse($paused1->isComplete()); // Did not pause again
        $this->assertFalse($paused2->isComplete()); // Did not pause again

        $this->assertAfterStart($loop, true, false);
        delay(0.001);
        $this->assertFinal($loop);

        delay(0.001);
        $this->assertFalse($paused1->isComplete()); // Did not pause again
        $this->assertFalse($paused2->isComplete()); // Did not pause again
    }

    /**
     * Provide resumable loop implementations.
     *
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
     *
     */
    public function provideResumableInterval(): Generator
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
