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
use danog\Loop\Test\Interfaces\BasicInterface;

/**
 * Fixtures.
 */
abstract class Fixtures extends AsyncTestCase
{
    const LOOP_NAME = 'PONY';
    /**
     * Check if promise has been resolved afterwards.
     *
     * @param Promise $promise Promise
     *
     * @return boolean
     */
    protected static function isResolved(Promise $promise): bool
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
    protected function assertPreStart(BasicInterface $loop)
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
    protected function assertAfterStart(BasicInterface $loop, bool $running = true)
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
    protected function assertFinal(BasicInterface $loop)
    {
        $this->assertTrue($loop->ran());
        $this->assertFalse($loop->isRunning());

        $this->assertTrue($loop->inited());

        $this->assertEquals(1, $loop->startCounter());
        $this->assertEquals(1, $loop->endCounter());
    }
}
