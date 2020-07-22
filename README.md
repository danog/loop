# IPC

[![Build Status](https://img.shields.io/travis/danog/loop/master.svg?style=flat-square)](https://travis-ci.com/danog/loop)
![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

`danog/loop` provides a very useful set of async loop APIs based on [AMPHP](https://amphp.org), for executing operations periodically or on demand, in background loops a-la threads.
It's a more flexible and powerful alternative to [AMPHP](https://amphp.org)'s [repeat](https://amphp.org/amp/event-loop/api#repeat) function, allowing dynamically changeable repeat periods, resumes and signaling.  

## Installation

```bash
composer require danog/loop
```

## Examples

All loop APIs are defined by a set of [interfaces](https://github.com/danog/loop/tree/master/lib/Interfaces): however, to use them, you would usually have to extend only one of the [abstract class implementations](https://github.com/danog/loop/tree/master/lib).  

### Loop

A basic loop, capable of running in background (asynchronously) the code contained in the `loop` function.  

[Interface](https://github.com/danog/loop/blob/master/lib/Interfaces/LoopInterface.php):  
```php
namespace danog\Loop\Interfaces;

interface LoopInterface
{
    /**
     * Start the loop.
     *
     * Returns false if the loop is already running.
     *
     * @return bool
     */
    public function start(): bool;
    /**
     * The actual loop function.
     *
     * @return \Generator
     */
    public function loop(): \Generator;
    /**
     * Get name of the loop.
     *
     * @return string
     */
    public function __toString(): string;
    /**
     * Check whether loop is running.
     *
     * @return boolean
     */
    public function isRunning(): bool;
}
```

Usually one would extend the [Loop implementation](https://github.com/danog/loop/blob/master/lib/Loop.php), you have to define only the `loop` function.  
The `loop` function will be run only once, every time the `start` method is called.  
Multiple calls to `start` will be ignored, returning `false` instead of `true`.  
You can use the `isRunning` method to check if the loop is already running.  
The `__toString` method still has to be implemented in order to get the name of the loop, that will be used by the MadelineProto logging mechanism every time the loop starts/exits/fails to start.  

```php
use danog\Loop\Loop;
class MyLoop extends Loop
{
    private $callable;
    public function __construct($API, $callable)
    {
        $this->API = $API;
        $this->callable = $callable;
    }
    public function loop()
    {
        $MadelineProto = $this->API;
        $logger = &$MadelineProto->logger;
        $callable = $this->callable;

        $result = null;
        while (true) {
            $params = yield $callable($result);
            $result = yield $MadelineProto->messages->sendMessage($params);
        }
    }
    public function __toString(): string
    {
        return "my custom loop";
    }
}
```

The loop can be instantiated and `start()`ed, and this will automatically run the code in the loop in background.  
If, however, only your loop is started (without an event handling loop), you have to pass the promise returned by `$loop->start()` to `$MadelineProto->loop`.  
Do NOT do this if you've already started `$MadelineProto->loop()`.  
```php
$loop = new MyLoop;
$MadelineProto->loop($loop->start());
```

### ResumableLoop

A way more useful loop that exposes APIs to pause and resume the execution of the loop, both from outside of the loop, and in a cron-like manner from inside of the loop.  

[Interface](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/ResumableLoopInterface.php):  
```php
namespace danog\Loop;

interface ResumableLoopInterface extends LoopInterface
{
    /**
     * Pause the loop.
     *
     * @param int $time For how long to pause the loop, if null will pause forever (until resume is called from outside of the loop)
     *
     * @return Promise
     */
    public function pause($time = null): Promise;

    /**
     * Resume the loop.
     *
     * @return void
     */
    public function resume();
}
```

Usually one would extend the [ResumableSignalLoop implementation](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/Impl/ResumableSignalLoop.php).  
An example implementation can be seen in the [ResumableSignalLoop section of this page](#resumablesignalloop).  

### SignalLoop

Yet another loop interface that exposes APIs to send signals to the loop, useful to force the termination of a loop from the outside, or to send data into it.  

[Interface](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/SignalLoopInterface.php):  
```php
namespace danog\Loop;

interface SignalLoopInterface extends LoopInterface
{
    /**
     * Resolve the promise or return|throw the signal.
     *
     * @param Promise $promise The origin promise
     *
     * @return Promise
     */
    public function waitSignal($promise): Promise;

    /**
     * Send a signal to the the loop.
     *
     * @param Exception|any $data Signal to send
     *
     * @return void
     */
    public function signal($data);
}

```

Usually one would extend the [ResumableSignalLoop implementation](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/Impl/ResumableSignalLoop.php).  
An example implementation can be seen in the [ResumableSignalLoop section of this page](#resumablesignalloop).  
If you want, you can also extend only the [SignalLoop implementation](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/Impl/SignalLoop.php), but usually a combination of the SignalLoop and ResumableLoop implementations is used, so read on to find out how to do that.  

### ResumableSignalLoop

This is what you would usually use to build a full async loop.  
All loop interfaces and loop implementations are combined into one single abstract class you can extend.  

```php
use danog\ResumableSignalLoop;
class MySuperLoop extends ResumableSignalLoop
{
    private $timeout;
    public function __construct($API, $timeout)
    {
        $this->API = $API;
        $this->timeout = $timeout;
    }
    public function loop()
    {
        $MadelineProto = $this->API;
        $logger = &$MadelineProto->logger;

        while (true) {
            $t = time();

            $result = yield $this->waitSignal($this->pause($this->timeout));
            if ($result <= 0) {
                return;
            } else if ($result > 0) {
                $this->timeout = $result;
            }
            
            $t = time() - $t;
            
            $result = yield $MadelineProto->messages->sendMessage(
                [
                    'peer'    => '...',
                    'message' => "Resumed after $t seconds of timeout"
                ]
            );
        }
    }
    public function __toString(): string
    {
        return "my cron signal loop";
    }
}
```

[ResumableSignalLoop implementation](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/Impl/ResumableSignalLoop.php).  
As with the [Loop](#loop).  

The difference now is that you can use the `pause` method to pause execution of the loop for a certain period of time (in seconds, supports decimals).  
If `null` is passed, execution will be suspended forever (or until `resume` is called from outside of the loop).  

If the promise returned by `pause` (or by any other async method) is passed to `waitSignal`, and the result is yielded, execution will be suspended for the specified amount of time|forever, or until a signal is received through the `signal` method.  
The passed signal will then be returned as result of the `waitSignal` method, and can be used to stop the loop, or simply as a message exchange mechanism.  

### GenericLoop

If you want a simpler way to use the `ResumableSignalLoop`, you can use the [GenericLoop](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/Generic/GenericLoop.php).  
The constructor accepts three parameters:
```php
    /**
     * Constructor
     *
     * @param \danog\API $API Instance of MadelineProto
     * @param callback $callback Callback to run
     * @param string $name Fetcher name
     */
    public function __construct($API, $callback, $name) { // ...
```

Example:
```php
use danog\Loop\Generic\GenericLoop;
$loop = new GenericLoop(
    $MadelineProto,
    function () {
        yield $this->API->messages->sendMessage(['peer' => '...', 'message' => 'Hi every 2 seconds']);

        return 2;
    },
    "My super loop"
);
$loop->start();
```
The callback will be bound to the GenericLoop instance: this means that you will be able to use `$this` as if the callback were actually the `loop` function (you can access the API property, use the pause/waitSignal methods & so on).  
The return value of the callable can be:  
* A number - the loop will be paused for the specified number of seconds
* `GenericLoop::STOP` - The loop will stop
* `GenericLoop::PAUSE` - The loop will pause forever (or until the `resume` method is called on the loop object from outside the loop)
* `GenericLoop::CONTINUE` - Return this if you want to rerun the loop without waiting

If the callable does not return anything, the loop will behave is if `GenericLoop::PAUSE` was returned.  

### PeriodicLoop

If you simply want to execute an action every N seconds, [PeriodicLoop](https://github.com/danog/MadelineProto/blob/master/src/danog/MadelineProto/Loop/Generic/PeriodicLoop.php) is the way to go.  
The constructor accepts four parameters:
```php
    /**
     * Constructor.
     *
     * @param \danog\API $API      Instance of MTProto class
     * @param callable                 $callback Callback to call
     * @param string                   $name     Loop name
     * @param int|float                $timeout  Loop timeout
     */
    public function __construct($API, callable $callback, string $name, $timeout) { // ...
```

Example:
```php
use danog\Loop\Generic\PeriodicLoop;
$loop = new PeriodicLoop(
    $MadelineProto,
    function () use (&$loop) {
        yield $this->API->messages->sendMessage(['peer' => '...', 'message' => 'Hi every 2 seconds']);
    },
    "My super loop",
    2
);
$loop->start();
```
Unlike `GenericLoop`, the callback will **not** be bound to the GenericLoop instance.
You can still command the loop by using the pause/waitSignal methods from the outside or by capturing the loop instance in a closure like shown above.  
