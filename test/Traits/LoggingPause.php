<?php
/**
 * Loop test trait.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace danog\Loop\Test\Traits;

use function Amp\delay;

trait LoggingPause
{
    use Logging;
    /**
     * Number of times loop was paused.
     *
     * @var integer
     */
    private $pauseCount = 0;
    /**
     * Last pause delay.
     *
     * @var int
     */
    private $lastPause = 0;
    /**
     * Get number of times loop was paused.
     *
     * @return integer
     */
    public function getPauseCount(): int
    {
        return $this->pauseCount;
    }

    /**
     * Get last pause.
     *
     * @return integer
     */
    public function getLastPause(): int
    {
        return $this->lastPause;
    }
    /**
     * Report pause, can be overriden for logging.
     *
     * @param integer $timeout Pause duration, 0 = forever
     *
     * @return void
     */
    protected function reportPause(int $timeout): void
    {
        parent::reportPause($timeout);
        $this->pauseCount++;
        $this->lastPause= $timeout;
    }
}
