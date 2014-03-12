<?php
namespace ZendQueueTest\Adapter;

use ZendQueue\Adapter\Db;
class DbTest extends AdapterTest
{

    /**
     * getAdapterName() is an method to help make AdapterTest work with any
     * new adapters
     *
     * You must overload this method
     *
     * @return string
     */
    public function getAdapterName()
    {
        return 'Db';
    }

    /**
     * getAdapterName() is an method to help make AdapterTest work with any
     * new adapters
     *
     * You may overload this method.  The default return is
     * 'Zend_Queue_Adapter_' . $this->getAdapterName()
     *
     * @return string
     */
    public function getAdapterFullName()
    {
        return '\ZendQueue\Adapter\\' . $this->getAdapterName();
    }

    public function getTestOptions()
    {
        return array('driverOptions' =>
            array(
                'driver' => 'Pdo',
                'dsn' => "mysql:dbname=".ZEND_DB_ADAPTER_DRIVER_MYSQL_DATABASE.";host=".ZEND_DB_ADAPTER_DRIVER_MYSQL_HOSTNAME,
                'username' => ZEND_DB_ADAPTER_DRIVER_MYSQL_USERNAME,
                'password' => ZEND_DB_ADAPTER_DRIVER_MYSQL_PASSWORD,
                'driver_options' => array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                ),
            ),
        );
    }

    public function testGetQueueTable()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $this->assertInstanceOf('Zend\Db\TableGateway\TableGateway', $queue->getAdapter()->getQueueTable());
    }

    public function testGetMessageTable()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $this->assertInstanceOf('Zend\Db\TableGateway\TableGateway', $queue->getAdapter()->getMessageTable());
    }

    public function testConnectWithInjectedAdapterAndGateway()
    {
        $testOptions = $this->getTestOptions();
        $driverOptions = $testOptions['driverOptions'];
        $dbAdapter = new \Zend\Db\Adapter\Adapter($driverOptions);

        $queueTableGateway = new \Zend\Db\TableGateway\TableGateway('queues', $dbAdapter);
        $msgTableGateway = new \Zend\Db\TableGateway\TableGateway('messages', $dbAdapter);

        $adapter = new Db();
        $adapter->setOptions(array(
            'dbAdapter'     => $dbAdapter,
            'queueTable'    => $queueTableGateway,
            'messageTable'  => $msgTableGateway,
        ));

        $this->assertTrue($adapter->connect());
        $this->assertSame($queueTableGateway, $adapter->getQueueTable());
        $this->assertSame($msgTableGateway, $adapter->getMessageTable());
    }

}