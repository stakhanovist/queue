<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter\Mongo;

use Stakhanovist\Queue\Adapter\Mongo\AbstractMongo;
use Stakhanovist\Queue\Parameter\ReceiveParameters;
use MongoCollection;
use Stakhanovist\Queue\Adapter\AbstractAdapter;
use Stakhanovist\Queue\Parameter\ReceiveParametersInterface;
use Stakhanovist\Queue\QueueInterface;

class ConcreteMongo extends AbstractMongo
{
    public function __construct($options = [])
    {
        //Bypass Mongo extension check
        AbstractAdapter::__construct($options);
    }

    public function setupCursor(MongoCollection $collection, ReceiveParametersInterface $params = null,
        $criteria = [self::KEY_HANDLE => false],
        array $fields = ['_id', self::KEY_HANDLE]
    ) {
        return parent::setupCursor($collection, $params, $criteria, $fields);
    }

    public function receiveMessageAtomic(QueueInterface $queue, MongoCollection $collection, $id)
    {
        return parent::receiveMessageAtomic($queue, $collection, $id);
    }
}
