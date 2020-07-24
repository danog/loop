<?php

require 'vendor/autoload.php';

use Amp\Loop as AmpLoop;
use danog\Loop\ResumableLoop;

use function Amp\delay;

class MyLoop extends ResumableLoop
{
    /**
     * Loop name.
     *
     * @var string
     */
    private $name;
    /**
     * Constructor.
     *
     * @param string   $name     Loop name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    /**
     * Main loop.
     *
     * @return \Generator
     */
    public function loop(): \Generator
    {
        $number = 0;
        while (true) {
            yield $this->pause(1000);
            echo "$this: $number".PHP_EOL;
            $number++;
        }
    }

    // Optionally, we can also define logging methods
    /**
     * Started loop.
     *
     * @return void
     */
    protected function startedLoop(): void
    {
        parent::startedLoop();
        echo "Started loop $this!".PHP_EOL;
    }
    /**
     * Exited loop.
     *
     * @return void
     */
    protected function exitedLoop(): void
    {
        parent::exitedLoop();
        echo "Exited loop $this!".PHP_EOL;
    }
    // End of logging methods

    /**
     * Get loop name.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}

AmpLoop::run(function () {
    $loops = [];
    for ($x = 0; $x < 10; $x++) {
        $loop = new MyLoop("Loop number $x");
        $loop->start();
        yield delay(100);
        $loops []= $loop;
    }
    yield delay(5000);
    echo "Resuming prematurely all loops!".PHP_EOL;
    foreach ($loops as $loop) {
        $loop->resume();
    }
});
