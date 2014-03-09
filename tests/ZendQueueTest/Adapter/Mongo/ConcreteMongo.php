<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueueTest\Adapter\Mongo;

use ZendQueue\Adapter\Mongo\AbstractMongo;
use ZendQueue\Parameter\ReceiveParameters;
use MongoCollection;
use ZendQueue\Adapter\AbstractAdapter;
use ZendQueue\SpecificationInterface as Queue;

class ConcreteMongo extends AbstractMongo
{
    public function __construct($options = array())
    {
        //Bypass Mongo extension check
        AbstractAdapter::__construct($options);
    }

    public function setupCursor(MongoCollection $collection, ReceiveParameters $params = null,
        $criteria = array(self::KEY_HANDLE => false),
        array $fields = array('_id', self::KEY_HANDLE)
    )
    {
        return parent::setupCursor($collection, $params, $criteria, $fields);
    }

    protected function receiveMessageAtomic(Queue $queue, MongoCollection $collection, $id)
    {
        return parent::receiveMessageAtomic($queue, $collection, $id);
    }

}