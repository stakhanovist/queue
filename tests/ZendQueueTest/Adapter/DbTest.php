<?php
namespace ZendQueueTest\Adapter;

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
                'dsn' => 'mysql:dbname=zfqueue;host=127.0.0.1',
                'username' => 'root',
                'password' => 'root',
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

}