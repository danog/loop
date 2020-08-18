# Loop

![Build status](https://github.com/danog/loop/workflows/build/badge.svg)
[![codecov](https://codecov.io/gh/danog/loop/branch/master/graph/badge.svg)](https://codecov.io/gh/danog/loop)
[![Psalm coverage](https://shepherd.dev/github/danog/loop/coverage.svg)](https://shepherd.dev/github/vimeo/shepherd)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

`danog/loop` provides a set of powerful async loop APIs for executing operations periodically or on demand, in background loops a-la threads.  
A more flexible and powerful alternative to [AMPHP](https://amphp.org)'s [repeat](https://amphp.org/amp/event-loop/api#repeat) function, allowing dynamically changeable repeat periods, resumes and signaling.  

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
  * [SignalLoop](#signalloop)
  * [ResumableSignalLoop](#resumablesignalloop)

All loop APIs are defined by a set of [interfaces](https://github.com/danog/loop/tree/master/lib/Interfaces): however, to use them, you would usually have to extend only one of the [abstract class implementations](https://github.com/danog/loop/tree/master/lib).  

### Loop

[Interface](https://github.com/danog/loop/blob/master/lib/Interfaces/LoopInterface.php) - [Example](https://github.com/danog/loop/blob/master/examples/2.%20Advanced/Loop.php)

A basic loop, capable of running in background (asynchronously) the code contained in the `loop` function.  

API:  
```php
namespace danog\Loop;

abstract class Loop
{
    abstract public function loop(): \Generator;
    abstract public function __toString(): string;
    
    public function start(): bool;
    public function isRunning(): bool;

    protected function startedLoop(): void;
    protected function exitedLoop(): void;
}
```

#### loop()

The `loop` [async coroutine](https://amphp.org/amp/coroutines/) will be run only once, every time the `start` method is called.  

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
    public function pause(?int $time = null): Promise;
    public function resume(): Promise;
}
```

All methods from [Loop](#loop), plus:

#### pause()

Pauses the loop for the specified number of milliseconds, or forever if `null` is provided.  

#### resume()

Forcefully resume the loop from the outside.  
Returns a promise that is resolved when the loop is paused again.  


### SignalLoop

[Interface](https://github.com/danog/loop/blob/master/lib/Interfaces/SignalLoopInterface.php) - [Example](https://github.com/danog/loop/blob/master/examples/2.%20Advanced/SignalLoop.php)

Yet another loop interface that exposes APIs to send signals to the loop, useful to force the termination of a loop from the outside, or to send data into it.  

```php
namespace danog\Loop;

abstract class SignalLoop extends Loop
{
    public function signal($what): void;
    public function waitSignal($promise): Promise;
}

```

All methods from [Loop](#loop), plus:

#### signal()

Sends a signal to the loop: can be anything, but typically `true` is often used as termination signal.  
Signaling can be used as a message exchange mechanism a-la IPC, and can also be used to throw exceptions inside the loop.  

#### waitSignal()

Resolve the provided promise or return|throw passed signal.  

### ResumableSignalLoop

[Interface](https://github.com/danog/loop/blob/master/lib/Interfaces/ResumableSignalLoopInterface.php) - [Example](https://github.com/danog/loop/blob/master/examples/2.%20Advanced/ResumableSignalLoop.php)

This is what you would usually use to build a full async loop.  
All loop interfaces and loop implementations are combined into a single class you can extend.  

```php
namespace danog\Loop;

abstract class ResumableSignalLoop extends SignalLoop, ResumableSignalLoop
{
}
```

The class is actually composited using traits to feature all methods from [SignalLoop](#signalloop) and [ResumableSignalLoop](#resumablesignalloop).  

### GenericLoop

[Class](https://github.com/danog/loop/blob/master/lib/Generic/GenericLoop.php) - [Example](https://github.com/danog/loop/blob/master/examples/1.%20Basic/GenericLoop.php)

If you want a simpler way to use the `ResumableSignalLoop`, you can use the GenericLoop.  
```php
namespace danog\Loop\Generic;
class GenericLoop extends ResumableSignalLoop
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
     * If possible, the callable will be bound to the current instance of the loop.
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
     *
     * @return string
     */
    public function __toString(): string;
}
```

The callback will be bound to the `GenericLoop` instance: this means that you will be able to use `$this` as if the callback were actually the `loop` function (you can get the loop name by casting `$this` to a string, use the pause/waitSignal methods & so on).  
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

class PeriodicLoop extends ResumableSignalLoop
{
    /**
     * Constructor.
     *
     * If possible, the callable will be bound to the current instance of the loop.
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
}
```

`PeriodicLoop` runs a callback at a periodic interval.  
The callback will be bound to the `PeriodicLoop` instance: this means that you will be able to use `$this` as if the callback were actually the `loop` function (you can get the loop name by casting `$this` to a string, use the pause/waitSignal methods & so on).  
The loop can be stopped from the outside or from the inside by signaling or returning `true`. 
