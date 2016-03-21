Stakhanovist Queue
------------------

[![Last Release](https://img.shields.io/packagist/v/stakhanovist/queue.svg?style=flat-square)](https://packagist.org/packages/stakhanovist/queue) [![BuildStatus](https://img.shields.io/travis/stakhanovist/queue/master.svg?style=flat-square)](https://travis-ci.org/stakhanovist/queue)

> Provides a factory function to create specific queue client objects.

This library aims to provide a [message queue](http://en.wikipedia.org/wiki/Message_queue) abstraction layer, usefull for inter-process comunication or for distributed processing (a complete job queue system can be implemented using the [worker library](https://github.com/stakhanovist/worker)).

Currenty, the following **message queue services** are supported:

- A **SQL database driven queue** via `Zend\Db`

    > Install [stakhanovist/queue-db](http://github.com/stakhanovist/queue-db)

- A **MongoDb database driven queue**, with message awaiting support ([using capped collection and tailable cursor](http://shtylman.com/post/the-tail-of-mongodb))

    > Install [stakhanovist/queue-mongodb](http://github.com/stakhanovist/queue-mongodb)

- A local array useful for non-persistent queues and for unit testing

    > Already included in this library


## Installation

Install it using [composer](http://getcomposer.org).

Add the following to your `composer.json` file:

```
"require": {
    "stakhanovist/queue": "~0.2.0",
}
```

## License

Stakhanovist Queue is licensed under the [BSD 2-Clause License](https://github.com/stakhanovist/queue/blob/master/LICENSE) except otherwise stated.

As this library initially started as a refactoring of the [**Zend\Queue** library](https://github.com/zendframework/ZendQueue) code, for source code parts still used the original [New BSD License](https://github.com/stakhanovist/queue/blob/master/LICENSE.ZF) is retained.

---

[![Analytics](https://ga-beacon.appspot.com/UA-49657176-4/queue?flat)](https://github.com/igrigorik/ga-beacon)
