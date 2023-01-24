<?php declare(strict_types=1);
/**
 * Loop test.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test;

use danog\Loop\Loop;
use danog\Loop\Test\Interfaces\BasicInterface;

/**
 * Fixtures.
 */
abstract class Fixtures extends \PHPUnit\Framework\TestCase
{
    const LOOP_NAME = 'TTTT';
    /**
     * Execute pre-start assertions.
     */
    protected function assertPreStart(BasicInterface&Loop $loop): void
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
     * @param bool           $running Whether we should expect the loop to be running
     * @param bool           $running Whether we should actually start the loop by returning control to the event loop
     *
     */
    protected function assertAfterStart(BasicInterface&Loop $loop, bool $running = true, bool $start = true): void
    {
        if ($start) {
            LoopTest::waitTick();
        }
        $this->assertTrue($loop->inited());

        if ($running) {
            $this->assertFalse($loop->ran());
        }
        $this->assertEquals($running, $loop->isRunning());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals($running ? 0 : 1, $loop->endCounter());

        $this->assertEquals($running, !$loop->start());
    }
    /**
     * Execute final assertions.
     */
    protected function assertFinal(BasicInterface&Loop $loop): void
    {
        $this->assertTrue($loop->ran());
        $this->assertFalse($loop->isRunning());

        $this->assertTrue($loop->inited());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());
    }
}
