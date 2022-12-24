<?php declare(strict_types=1);
/**
 * Loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use Amp\Future;
use Amp\PHPUnit\AsyncTestCase;
use danog\Loop\Test\Interfaces\BasicInterface;

use function Amp\async;

/**
 * Fixtures.
 */
abstract class Fixtures extends AsyncTestCase
{
    const LOOP_NAME = 'PONY';
    /**
     * Check if promise has been resolved afterwards.
     */
    protected static function isResolved(Future $promise): bool
    {
        $resolved = false;
        async(function () use ($promise, &$resolved): void {
            $promise->await();
            $resolved = true;
        });
        return $resolved;
    }
    /**
     * Execute pre-start assertions.
     *
     * @param BasicInterface $loop Loop
     *
     */
    protected function assertPreStart(BasicInterface $loop): void
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
     */
    protected function assertAfterStart(BasicInterface $loop, bool $running = true): void
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
     */
    protected function assertFinal(BasicInterface $loop): void
    {
        $this->assertTrue($loop->ran());
        $this->assertFalse($loop->isRunning());

        $this->assertTrue($loop->inited());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());
    }
}
