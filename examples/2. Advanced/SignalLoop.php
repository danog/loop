<?php declare(strict_types=1);

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
     */
    public function loop(): void
    {
        $number = 0;
        while (true) {
            if ($this->waitSignal(delay(1000))) {
                echo "Got exit signal in $this!".PHP_EOL;
                return;
            }
            echo "$this: $number".PHP_EOL;
            $number++;
        }
    }
    /**
     * Get loop name.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}

/** @var SigLoop[] */
$loops = [];
for ($x = 0; $x < 10; $x++) {
    $loop = new SigLoop("Loop number $x");
    $loop->start();
    delay(100);
    $loops []= $loop;
}
delay(5000);
echo "Closing all loops!".PHP_EOL;
foreach ($loops as $loop) {
    $loop->signal(true);
}
