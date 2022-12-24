<?php declare(strict_types=1);

require 'vendor/autoload.php';

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
     */
    public function loop(): void
    {
        $number = 0;
        while (true) {
            $this->pause(1000);
            echo "$this: $number".PHP_EOL;
            $number++;
        }
    }

    // Optionally, we can also define logging methods
    /**
     * Started loop.
     *
     */
    protected function startedLoop(): void
    {
        parent::startedLoop();
        echo "Started loop $this!".PHP_EOL;
    }
    /**
     * Exited loop.
     *
     */
    protected function exitedLoop(): void
    {
        parent::exitedLoop();
        echo "Exited loop $this!".PHP_EOL;
    }
    // End of logging methods

    /**
     * Get loop name.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}

$loops = [];
for ($x = 0; $x < 10; $x++) {
    $loop = new MyLoop("Loop number $x");
    $loop->start();
    delay(0.1);
    $loops []= $loop;
}
delay(5);
echo "Resuming prematurely all loops!".PHP_EOL;
foreach ($loops as $loop) {
    $loop->resume();
}
delay(2);
