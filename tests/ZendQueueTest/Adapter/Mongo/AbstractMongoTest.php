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
use ZendQueue\Queue;
use ZendQueue\Adapter\Null;
/**
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage UnitTests
 * @group      Zend_Queue
 */
class AbstractMongoTest extends \PHPUnit_Framework_TestCase
{

    protected $abstractMongo;

    public function setUp()
    {

        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('The mongo PHP extension is not available');
        }

        $this->database = 'zendqueue_mongoabstract_test';
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


        $this->abstractMongo = new ConcreteMongo(array(
            'mongoDb' => $this->mongoDb
        ));
    }

    public function tearDown()
    {

    }

    public function testConnect()
    {
        //Test with params
        $abstractMongo = new ConcreteMongo(array(
            'driverOptions' => array(
                'db' => $this->database,
                'options' => array("connect" => TRUE)
            )
        ));

        $this->assertTrue($abstractMongo->connect());

        $abstractMongo = new ConcreteMongo(array(
            'driverOptions' => array(
                'dsn' => 'mongodb://localhost:27017/' . $this->database
            )
        ));

        $this->assertTrue($abstractMongo->connect());

        //Test passing MongoDB instance
        $this->assertTrue($this->abstractMongo->connect());

        //Test invalid options excepetion
        $this->setExpectedException('ZendQueue\Exception\InvalidArgumentException');

        $abstractMongo = new ConcreteMongo();
        $abstractMongo->connect();

    }

    public function testReceiveMessageAtomicWithNoMessage()
    {
        //assume queue is empty
        $queue = new Queue('foo', new Null());
        $this->abstractMongo->connect();
        $this->assertNull($this->abstractMongo->receiveMessageAtomic($queue, $this->mongoDb->selectCollection('non-existing-collection'), new \MongoId()));
    }

}