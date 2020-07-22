<?php

require 'vendor/autoload.php';

use Amp\Loop as AmpLoop;
use danog\Loop\Loop;

use function Amp\delay;

class MyLoop extends Loop
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
        while (true) {
            $number = yield from $callable($number);
            echo "$this: $number".PHP_EOL;
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
    $function = function (int $number): \Generator {
        yield delay(1000);
        return $number + 1;
    };
    $loops = [];
    for ($x = 0; $x < 10; $x++) {
        $loop = new MyLoop($function, "Loop number $x");
        $loop->start();
        yield delay(100);
        $loops []= $loop;
    }
});
