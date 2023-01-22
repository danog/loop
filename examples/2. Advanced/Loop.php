<?php declare(strict_types=1);

require 'vendor/autoload.php';

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
     */
    public function loop(): void
    {
        $callable = $this->callable;

        $number = 0;
        while (true) {
            $number = $callable($number);
            echo "$this: $number".PHP_EOL;
        }
    }

    // Optionally, we can also define logging methods
    /**
     * Started loop.
     *
     */
    protected function startedLoop(): void
    {
        echo "Started loop $this!".PHP_EOL;
    }
    /**
     * Exited loop.
     *
     */
    protected function exitedLoop(): void
    {
        echo "Exited loop $this!".PHP_EOL;
    }
    // End of logging methods

    /**
     * Get loop name.
     *
     */
    public function __toString(): string
    {
        return $this->name;
    }
}

$function = function (int $number) {
    delay(1);
    return $number + 1;
};
$loops = [];
for ($x = 0; $x < 10; $x++) {
    $loop = new MyLoop($function, "Loop number $x");
    $loop->start();
    delay(0.1);
    $loops []= $loop;
}
