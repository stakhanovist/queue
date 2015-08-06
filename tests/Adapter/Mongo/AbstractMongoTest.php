<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter\Mongo;

use Stakhanovist\Queue\Adapter\MongoCappedCollection;
use Stakhanovist\Queue\Adapter\Null;
use Stakhanovist\Queue\Queue;

/**
 *
 * @group      Stakhanovist_Queue
 */
class AbstractMongoTest extends \PHPUnit_Framework_TestCase
{
    protected $mongo;

    protected $abstractMongo;

    protected $database;

    protected $collection;

    protected $mongoDb;

    public function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('The mongo PHP extension is not available');
        }

        $this->database = 'stakhanovist_queue_mongoabstract_test';
        $this->collection = 'queue';

        $mongoClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? 'Mongo' : 'MongoClient';

//         $this->mongo = $this->getMockBuilder($mongoClass)
// //             ->disableOriginalConstructor()
//             ->setMethods(array('selectDB'))
//             ->getMock();

//         $this->mongoDb = $this->getMockBuilder('MongoDB')
// //         ->disableOriginalConstructor()
//         ->setMethods(array('save', 'createCollection'))
//         ->getMock();


        $this->mongo = new \MongoClient();
        $this->mongoDb = $this->mongo->selectDb($this->database);
        $this->mongoDb->drop(); //cleanup


        $this->abstractMongo = new ConcreteMongo(
            [
            'mongoDb' => $this->mongoDb
            ]
        );
    }



    public function testConnect()
    {
        //Test with params
        $abstractMongo = new ConcreteMongo(
            [
            'driverOptions' => [
                'db' => $this->database,
                'options' => ["connect" => true]
            ]
            ]
        );

        $this->assertTrue($abstractMongo->connect());

        $abstractMongo = new ConcreteMongo(
            [
            'driverOptions' => [
                'dsn' => 'mongodb://localhost:27017/' . $this->database
            ]
            ]
        );

        $this->assertTrue($abstractMongo->connect());

        //Test passing MongoDB instance
        $this->assertTrue($this->abstractMongo->connect());

        //Test invalid options excepetion
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');

        $abstractMongo = new ConcreteMongo();
        $abstractMongo->connect();
    }

    public function testGetMongoDb()
    {
        $this->abstractMongo->connect();
        $this->assertSame($this->mongoDb, $this->abstractMongo->getMongoDb());
    }

    public function testShouldThrowExceptionOnGetMongoDbBeforeConnect()
    {
        $this->setExpectedException('Stakhanovist\Queue\Exception\ConnectionException');
        $this->abstractMongo->getMongoDb();
    }

    public function testShouldThrowExceptionOnExistingCappedCollection()
    {
        $mongoCappedAdapter = new MongoCappedCollection();
        $mongoCappedAdapter->setOptions($this->abstractMongo->getOptions());
        $mongoCappedAdapter->connect();
        $mongoCappedAdapter->createQueue('testExistingCappedCollection');

        $this->abstractMongo->connect();
        $this->setExpectedException('Stakhanovist\Queue\Exception\RuntimeException');
        $this->abstractMongo->queueExists('testExistingCappedCollection');
    }

    public function testReceiveMessageAtomicWithNoMessage()
    {
        //assume queue is empty
        $queue = new Queue('foo', new Null());
        $this->abstractMongo->connect();
        $this->assertNull($this->abstractMongo->receiveMessageAtomic($queue, $this->mongoDb->selectCollection('non-existing-collection'), new \MongoId()));
    }
}
