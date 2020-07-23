<?php

require 'vendor/autoload.php';

use Amp\Loop;
use danog\Loop\SignalLoop;

use function Amp\delay;

class SigLoop extends SignalLoop
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
            if (yield $this->waitSignal(delay(1000))) {
                echo "Got exit signal in $this!".PHP_EOL;
                return;
            }
            echo "$this: $number".PHP_EOL;
            $number++;
        }
    }
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

Loop::run(function () {
    /** @var SigLoop[] */
    $loops = [];
    for ($x = 0; $x < 10; $x++) {
        $loop = new SigLoop("Loop number $x");
        $loop->start();
        yield delay(100);
        $loops []= $loop;
    }
    yield delay(5000);
    echo "Closing all loops!".PHP_EOL;
    foreach ($loops as $loop) {
        $loop->signal(true);
    }
});
