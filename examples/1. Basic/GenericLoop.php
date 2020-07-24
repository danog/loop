<?php

require 'vendor/autoload.php';

use Amp\Loop;
use danog\Loop\Generic\GenericLoop;

use function Amp\delay;

Loop::run(function () {
    /** @var GenericLoop[] */
    $loops = [];
    for ($x = 0; $x < 10; $x++) {
        $callable = function () {
            static $number = 0;
            echo "$this: $number".PHP_EOL;
            $number++;
            return $number < 10 ? 1000 : GenericLoop::STOP;
        };
        $loop = new GenericLoop($callable, "Loop number $x");
        $loop->start();
        yield delay(100);
        $loops []= $loop;
    }
    yield delay(5000);
    echo "Resuming prematurely all loops!".PHP_EOL;
    foreach ($loops as $loop) {
        $loop->resume();
    }
    echo "OK done, waiting 5 more seconds!".PHP_EOL;
    yield delay(5000);
    echo "Closing all loops!".PHP_EOL;
    yield delay(10);
});
