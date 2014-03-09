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

        $this->database = 'zendqueue_test';
        $this->collection = 'queue';

        $mongoClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? 'Mongo' : 'MongoClient';

        $this->mongo = $this->getMockBuilder($mongoClass)
            ->disableOriginalConstructor()
            ->setMethods(array('selectDB'))
            ->getMock();

        $this->mongoDb = $this->getMockBuilder('MongoDB')
        ->disableOriginalConstructor()
        ->setMethods(array('save'))
        ->getMock();

        $this->abstractMongo = new ConcreteMongo(array(
            'mongoDb' => $this->mongoDb
        ));
    }

    public function tearDown()
    {

    }

    public function testConnect()
    {
        $this->assertTrue($this->abstractMongo->connect());
    }

}