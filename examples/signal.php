<?php

require 'vendor/autoload.php';

use Amp\Loop;
use danog\Loop\SignalLoop;

use function Amp\delay;

class SigLoop extends SignalLoop
{
    /**
     * Callable.
     *
     * @var callable
     */
    private $callable;
    /**
     * Loop name.
     *
     * @var string
     */
    private $name;
    /**
     * Constructor.
     *
     * @param callable $callable Callable
     * @param string   $name     Loop name
     */
    public function __construct(callable $callable, string $name)
    {
        $this->callable = $callable;
        $this->name = $name;
    }
    /**
     * Main loop.
     *
     * @return \Generator
     */
    public function loop(): \Generator
    {
        $callable = $this->callable;

        $number = 0;
        while ('stop' !== $number = yield $this->waitSignal($callable($number))) {
            echo "$this: $number".PHP_EOL;
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
    $function = function (int $number): \Generator {
        yield delay(1000);
        return $number + 1;
    };
    /** @var SigLoop[] */
    $loops = [];
    for ($x = 0; $x < 10; $x++) {
        $loop = new SigLoop($function, "Loop number $x");
        $loop->start();
        yield delay(100);
        $loops []= $loop;
    }
    yield delay(5000);
    foreach ($loops as $loop) {
        $loop->signal('stop');
    }
});
