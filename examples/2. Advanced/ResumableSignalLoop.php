<?php declare(strict_types=1);

require 'vendor/autoload.php';

use Amp\Loop;
use danog\Loop\ResumableSignalLoop;

use function Amp\delay;

class ResSigLoop extends ResumableSignalLoop
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
            if ($this->waitSignal($this->pause(1000))) {
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
     */
    public function __toString(): string
    {
        return $this->name;
    }
}

/** @var ResSigLoop[] */
$loops = [];
for ($x = 0; $x < 10; $x++) {
    $loop = new ResSigLoop("Loop number $x");
    $loop->start();
    delay(100);
    $loops []= $loop;
}
delay(5000);
echo "Resuming prematurely all loops!".PHP_EOL;
foreach ($loops as $loop) {
    $loop->resume();
}
echo "OK done, waiting 5 more seconds!".PHP_EOL;
delay(5000);
echo "Closing all loops!".PHP_EOL;
foreach ($loops as $loop) {
    $loop->signal(true);
}
