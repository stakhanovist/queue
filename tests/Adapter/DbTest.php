<?php
namespace StakhanovistQueueTest\Adapter;

use Stakhanovist\Queue\Adapter;
use Stakhanovist\Queue\Adapter\Db;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;

/**
 * Class DbTest
 */
class DbTest extends AdapterTest
{
    /**
     * getAdapterName() is an method to help make AdapterTest work with any
     * new adapters
     *
     * You may overload this method.
     * The default return is 'Stakhanovist_Queue_Adapter_' . $this->getAdapterName()
     *
     * @return string
     */
    public function getAdapterFullName()
    {
        return 'Stakhanovist\Queue\Adapter\\' . $this->getAdapterName();
    }

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

    public function testGetQueueTable()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $this->assertInstanceOf(TableGateway::class, $queue->getAdapter()->getQueueTable());
    }

    public function testGetMessageTable()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $this->assertInstanceOf(TableGateway::class, $queue->getAdapter()->getMessageTable());
    }

    public function testConnectWithInjectedAdapterAndGateway()
    {
        $testOptions = $this->getTestOptions();
        $driverOptions = $testOptions['driverOptions'];
        $dbAdapter = new ZendDbAdapter($driverOptions);

        $queueTableGateway = new TableGateway('queues', $dbAdapter);
        $msgTableGateway = new TableGateway('messages', $dbAdapter);

        $adapter = new Db();
        $adapter->setOptions(
            [
                'dbAdapter' => $dbAdapter,
                'queueTable' => $queueTableGateway,
                'messageTable' => $msgTableGateway,
            ]
        );

        $this->assertTrue($adapter->connect());
        $this->assertSame($queueTableGateway, $adapter->getQueueTable());
        $this->assertSame($msgTableGateway, $adapter->getMessageTable());
    }


    /**
     * @expectedException \Exception
     */
    public function testConnectWithConnectionException()
    {
        $testOptions = $this->getTestOptions();
        $driverOptions = $testOptions['driverOptions'];
        $dbAdapter = new ZendDbAdapter($driverOptions);

        $queueTableGateway = new TableGateway('queues', $dbAdapter);
        $msgTableGateway = new TableGateway('messages', $dbAdapter);

        $adapter = new Db();
        $adapter->setOptions(
            [
                'queueTable' => $queueTableGateway,
                'messageTable' => $msgTableGateway,
            ]
        );

        $adapter->connect();
    }


    public function testDeleteQueueWithoutQueue()
    {
        $queue = $this->createQueue(__FUNCTION__);

        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, ['createQueue', 'deleteQueue']);

        /** @var \Zend\Db\TableGateway\TableGateway $queueTable */
        $queueTable = $adapter->getQueueTable();

        $queueTable->delete(['queue_id' => $adapter->getQueueId($queue->getName())]);
        $this->assertFalse($adapter->deleteQueue($queue->getName()));
    }

    /**
     * @return array
     */
    public function getTestOptions()
    {
        if (ZEND_DB_ADAPTER_PDO_DRIVER == 'mysql') {
            $conf = [
                'driver' => 'Pdo_mysql',
                'dsn' => 'mysql:dbname=' . ZEND_DB_ADAPTER_DRIVER_MYSQL_DATABASE .
                         ';host=' . ZEND_DB_ADAPTER_DRIVER_MYSQL_HOSTNAME,
                'username' => ZEND_DB_ADAPTER_DRIVER_MYSQL_USERNAME,
                'password' => ZEND_DB_ADAPTER_DRIVER_MYSQL_PASSWORD,
                'driver_options' => [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                ],
            ];
        } elseif (ZEND_DB_ADAPTER_PDO_DRIVER == 'pgsql') {
            $conf = [
                'driver' => 'pdo_pgsql',
                'dsn' => 'pgsql:dbname=' . ZEND_DB_ADAPTER_DRIVER_PGSQL_DATABASE .
                         ';host=' . ZEND_DB_ADAPTER_DRIVER_PGSQL_HOSTNAME,
                'username' => ZEND_DB_ADAPTER_DRIVER_PGSQL_USERNAME,
                'password' => ZEND_DB_ADAPTER_DRIVER_PGSQL_PASSWORD,
            ];
        } elseif (ZEND_DB_ADAPTER_PDO_DRIVER == 'sqlite') {
            $conf = [
                'driver' => 'Pdo_sqlite',
                'database' => ZEND_DB_ADAPTER_DRIVER_SQLITE_DBPATH,
            ];
        } elseif (ZEND_DB_ADAPTER_PDO_DRIVER == 'sqlsrv') {
            $conf = [
                'driver' => 'Pdo',
                'dsn' => 'sqlsrv:Server=' . ZEND_DB_ADAPTER_DRIVER_SQLSRV_SERVER .
                         ';Database=' . ZEND_DB_ADAPTER_DRIVER_SQLSRV_DATABASE,
                'username' => ZEND_DB_ADAPTER_DRIVER_SQLSRV_USERNAME,
                'password' => ZEND_DB_ADAPTER_DRIVER_SQLSRV_PASSWORD,
            ];
        }
        return ['driverOptions' => $conf];
    }
}
