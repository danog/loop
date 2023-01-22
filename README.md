# Loop

![Build status](https://github.com/danog/loop/workflows/build/badge.svg)
[![codecov](https://codecov.io/gh/danog/loop/branch/master/graph/badge.svg)](https://codecov.io/gh/danog/loop)
[![Psalm coverage](https://shepherd.dev/github/danog/loop/coverage.svg)](https://shepherd.dev/github/vimeo/shepherd)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

`danog/loop` provides a set of powerful async loop APIs for executing operations periodically or on demand, in background loops a-la threads.  
A more flexible and powerful alternative to [AMPHP](https://amphp.org)'s [repeat](https://amphp.org/amp/event-loop/api#repeat) function, allowing dynamically changeable repeat periods and resumes.  

## Installation

```bash
composer require danog/loop
```

## API

* Basic
  * [GenericLoop](#genericloop)
  * [PeriodicLoop](#periodicloop)
* Advanced
  * [Loop](#loop)
  * [ResumableLoop](#resumableloop)

All loop APIs are defined by a set of [interfaces](https://github.com/danog/loop/tree/master/lib/Interfaces): however, to use them, you would usually have to extend only one of the [abstract class implementations](https://github.com/danog/loop/tree/master/lib).  

### Loop

[Interface](https://github.com/danog/loop/blob/master/lib/Interfaces/LoopInterface.php) - [Example](https://github.com/danog/loop/blob/master/examples/2.%20Advanced/Loop.php)

A basic loop, capable of running in background (asynchronously) the code contained in the `loop` function.  

API:  
```php
namespace danog\Loop;

abstract class Loop
{
    abstract public function loop();
    abstract public function __toString(): string;
    
    public function start(): bool;
    public function isRunning(): bool;

    protected function startedLoop(): void;
    protected function exitedLoop(): void;
}
```

#### loop()

The `loop` [async fiber](https://amphp.org/) will be run only once, every time the `start` method is called.  

#### __toString()

This method should return the loop's name.  
It's useful for implementing the [log methods](#startedloop).  

#### start()

Asynchronously starts the `loop` methods once in background.  
Multiple calls to `start` will be ignored, returning `false` instead of `true`.  

#### isRunning()

You can use the `isRunning` method to check if the loop is already running.  

#### startedLoop()

Optionally, you can override this method to detect and log when the loop is started.  
Make sure to always call the parent `startedLoop()` method to avoid issues.  
You can use directly `$this` as loop name when logging, thanks to the custom [__toString](#__tostring) method.  

#### exitedLoop()

Optionally, you can override this method to detect and log when the loop is ended.  
Make sure to always call the parent `exitedLoop()` method to avoid issues.  
You can use directly `$this` as loop name when logging, thanks to the custom [__toString](#__tostring) method.  


### ResumableLoop

[Interface](https://github.com/danog/loop/blob/master/lib/Interfaces/ResumableLoopInterface.php) - [Example](https://github.com/danog/loop/blob/master/examples/2.%20Advanced/ResumableLoop.php)

A way more useful loop that exposes APIs to pause and resume the execution of the loop, both from outside of the loop, and in a cron-like manner from inside of the loop.  

```php
namespace danog\Loop;

abstract class ResumableLoop extends Loop
{
    public function pause(?int $time = null): Future;
    public function resume(): Future;
}
```

All methods from [Loop](#loop), plus:

#### pause()

Pauses the loop for the specified number of milliseconds, or forever if `null` is provided.  

#### resume()

Forcefully resume the loop from the outside.  
Returns a future that is resolved when the loop is paused again.  

### GenericLoop

[Class](https://github.com/danog/loop/blob/master/lib/Generic/GenericLoop.php) - [Example](https://github.com/danog/loop/blob/master/examples/1.%20Basic/GenericLoop.php)

If you want a simpler way to use the `ResumableLoop`, you can use the GenericLoop.  
```php
namespace danog\Loop\Generic;
class GenericLoop extends ResumableLoop
{
    /**
     * Stop the loop.
     */
    const STOP = -1;
    /**
     * Pause the loop.
     */
    const PAUSE = null;
    /**
     * Rerun the loop.
     */
    const CONTINUE = 0;
    /**
     * Constructor.
     *
     * @param callable $callable Callable to run
     * @param string   $name     Loop name
     */
    public function __construct(callable $callable, string $name);
    /**
     * Report pause, can be overriden for logging.
     *
     * @param integer $timeout Pause duration, 0 = forever
     *
     * @return void
     */
    protected function reportPause(int $timeout): void;
    /**
     * Get loop name, provided to constructor.
     */
    public function __toString(): string;
    /**
     * Stops loop.
     */
    public function stop(): void;
}
```

The return value of the callable can be:  
* A number - the loop will be paused for the specified number of seconds
* `GenericLoop::STOP` - The loop will stop
* `GenericLoop::PAUSE` - The loop will pause forever (or until the `resume` method is called on the loop object from outside the loop)
* `GenericLoop::CONTINUE` - Return this if you want to rerun the loop without waiting

If the callable does not return anything, the loop will behave is if `GenericLoop::PAUSE` was returned.  

### PeriodicLoop

[Class](https://github.com/danog/loop/blob/master/lib/Generic/PeriodicLoop.php) - [Example](https://github.com/danog/loop/blob/master/examples/1.%20Basic/PeriodicLoop.php)

If you simply want to execute an action every N seconds, [PeriodicLoop](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/Generic/PeriodicLoop.php) is the way to go.  
```php
namespace danog\Loop\Generic;

class PeriodicLoop extends ResumableLoop
{
    /**
     * Constructor.
     *
     * @param callable $callback Callback to call
     * @param string   $name     Loop name
     * @param ?int     $interval Loop interval
     */
    public function __construct(callable $callback, string $name, ?int $interval);
    /**
     * Get name of the loop, passed to the constructor.
     *
     * @return string
     */
    public function __toString(): string;
    /**
     * Stops loop.
     */
    public function stop(): void;
}
```

`PeriodicLoop` runs a callback at a periodic interval.  
The loop can be stopped from the outside by using `stop()` and from the inside by returning `true`. 
