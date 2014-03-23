<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueue\Adapter;

use Zend\Db as ZendDb;
use Zend\Stdlib\MessageInterface;
use ZendQueue\Exception;
use ZendQueue\QueueInterface as Queue;
use ZendQueue\Adapter\Capabilities\CountMessagesCapableInterface;
use ZendQueue\Adapter\Capabilities\DeleteMessageCapableInterface;
use ZendQueue\Adapter\Capabilities\ListQueuesCapableInterface;
use ZendQueue\Parameter\SendParameters;
use ZendQueue\Parameter\ReceiveParameters;
use ZendQueue\Adapter\AbstractAdapter;
use Zend\Db\Sql\Sql;
use ZendQueue\Message\MessageIterator;

/**
 * Class for using connecting to a Zend_DB-based queuing system
 *
 */
class Db extends AbstractAdapter implements
    CountMessagesCapableInterface,
    DeleteMessageCapableInterface,
    ListQueuesCapableInterface
{
    /**
     * Default options
     *
     * @var array
     */
    protected $defaultOptions = array(
        'queueTable' => 'queue',
        'messageTable' => 'message'
    );

    /**
     * Internal array of queues to save on lookups
     *
     * @var array
     */
    protected $queues = array();

    /**
     * @var ZendDb\TableGateway\AbstractTableGateway
     */
    protected $queueTable = null;

    /**
     * @var ZendDb\TableGateway\AbstractTableGateway
     */
    protected $messageTable = null;

    /**
     * @var ZendDb\Adapter\Adapter
     */
    protected $adapter = null;

    /**
     * Get the TableGateway implementation of the queue table
     *
     * @return ZendDb\TableGateway\AbstractTableGateway
     */
    public function getQueueTable()
    {
        return $this->queueTable;
    }

    /**
     * Get the TableGateway implementation of the message table
     *
     * @return ZendDb\TableGateway\AbstractTableGateway
     */
    public function getMessageTable()
    {
        return $this->messageTable;
    }

    /**
     * @return array
     */
    public function getAvailableSendParams()
    {
        return array(
            SendParameters::SCHEDULE,
            SendParameters::REPEATING_INTERVAL,
        );
    }

    /**
     * @return array
     */
    public function getAvailableReceiveParams()
    {
        return array(
            ReceiveParameters::CLASS_FILTER,
            ReceiveParameters::VISIBILITY_TIMEOUT,
            ReceiveParameters::PEEK_MODE,
        );
    }

    /**
     * Connect (or refresh connection) to the db adapter
     *
     * Throws an exception if the adapter cannot connect to DB.
     *
     * @return bool
     * @throws Exception\ConnectionException
     */
    public function connect()
    {
        $options = $this->getOptions();

        if (isset($options['dbAdapter']) && $options['dbAdapter'] instanceof ZendDb\Adapter\Adapter) {
            $this->adapter = $options['dbAdapter'];
        } else {
            try {
                $this->adapter = new ZendDb\Adapter\Adapter($options['driverOptions']);
            } catch (\Exception $e) {
                if ($e instanceof ZendDb\Exception\ExceptionInterface) {
                    throw new Exception\ConnectionException('Error connecting to database: ' . $e->getMessage(), $e->getCode(), $e);
                }
                throw $e;
            }
        }

        if ($options['queueTable'] instanceof ZendDb\TableGateway\AbstractTableGateway) {
            $this->queueTable = $options['queueTable'];
        } else {
            $this->queueTable = new ZendDb\TableGateway\TableGateway($options['queueTable'], $this->adapter);
        }

        if ($options['messageTable'] instanceof ZendDb\TableGateway\AbstractTableGateway) {
            $this->messageTable = $options['messageTable'];
        } else {
            $this->messageTable = new ZendDb\TableGateway\TableGateway($options['messageTable'], $this->adapter);
        }

        return true;
    }

    /********************************************************************
     * Queue management functions
     *********************************************************************/

    /**
     * Get the queue ID
     *
     * Returns the queue's row identifier.
     *
     * @param  string $name
     * @return integer|null
     * @throws Exception\QueueNotFoundException
     */
    public function getQueueId($name)
    {
        if (array_key_exists($name, $this->queues)) {
            return $this->queues[$name];
        }

        $result = $this->getQueueTable()->select(array('queue_name' => $name));
        foreach ($result as $one) {
            $this->queues[$name] = (int)$one['queue_id'];
        }

        if (!array_key_exists($name, $this->queues)) {
            throw new Exception\QueueNotFoundException('Queue does not exist: ' . $name);
        }

        return $this->queues[$name];
    }


    /**
     * Check if a queue exists
     *
     * @param  string $name
     * @return boolean
     */
    public function queueExists($name)
    {
        $id = 0;

        try {
            $id = $this->getQueueId($name);
        } catch (\Exception $e) {
            return false;
        }

        return ($id > 0);
    }

    /**
     * Create a new queue
     *
     * @param  string $name    queue name
     * @return boolean
     * @throws Exception\RuntimeException - database error
     */
    public function createQueue($name)
    {
        if ($this->queueExists($name)) {
            return false;
        }

        try {
            $result = $this->getQueueTable()->insert(array('queue_name' => $name));
        } catch (\Exception $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return ((bool)$result);
    }

    /**
     * Delete a queue and all of it's messages
     *
     * Returns false if the queue is not found, true if the queue exists
     *
     * @param  string $name queue name
     * @return boolean
     * @throws Exception\RuntimeException - database error
     */
    public function deleteQueue($name)
    {
        try {
            $id = $this->getQueueId($name); // get primary key
        } catch(Exception\QueueNotFoundException $e){
            return false;
        }

        // if the queue does not exist then it must already be deleted.
        $list = $this->getQueueTable()->select(array('queue_id' => $id));
        if (count($list) === 0) {
            return false;
        }

        try {
            $this->getQueueTable()->delete(array('queue_id' => $id));
        } catch (\Exception $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        if (array_key_exists($name, $this->queues)) {
            unset($this->queues[$name]);
        }

        return true;
    }

    /**
     * Get an array of all available queues
     *
     * @return array
     * @throws Exception\ExceptionInterface - database error
     */
    public function listQueues()
    {
        $result = $this->getQueueTable()->select();
        foreach ($result as $one) {
            $this->queues[$one['queue_name']] = (int)$one['queue_id'];
        }

        $list = array_keys($this->queues);

        return $list;
    }

    /**
     * Return the approximate number of messages in the queue
     *
     * @param  Queue $queue
     * @return integer
     * @throws Exception\ExceptionInterface
     */
    public function countMessages(Queue $queue)
    {
        $sql = $this->getMessageTable()->getSql();

        $countSelect = new ZendDb\Sql\Select();
        $countSelect->columns(array('c' => new ZendDb\Sql\Expression('COUNT(1)')))
                    ->from(array('original_select' => $sql->select()))
                    ->where(array('queue_id' => $this->getQueueId($queue->getName())));

        $statement = $sql->prepareStatementForSqlObject($countSelect);
        $result    = $statement->execute();
        $row       = $result->current();

        return $row['c'];
    }

    /********************************************************************
     * Messsage management functions
     *********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param  Queue $queue
     * @param  MessageInterface $message Message to send to the active queue
     * @param  SendParameters $params
     * @return MessageInterface
     * @throws Exception\QueueNotFoundException
     * @throws Exception\RuntimeException - database error
     */
    public function sendMessage(Queue $queue, MessageInterface $message, SendParameters $params = null)
    {

        $this->cleanMessageInfo($queue, $message);

        $msg = array(
            'queue_id' => $this->getQueueId($queue->getName()),
            'created' => time(),
            'class' => get_class($message),
            'content' => (string)$message->getContent(),
            'metadata' => serialize($message->getMetadata()),
            'md5' => md5($message->toString())
        );

        if ($params) {
            if ($params->getSchedule()) {
                $msg['schedule'] = $params->getSchedule();
            }

            if ($params->getRepeatingInterval()) {
                $msg['interval'] = $params->getRepeatingInterval();
            }
        }

        try {
            $affectedRows = $this->getMessageTable()->insert($msg);
        } catch (\Exception $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$affectedRows) {
            throw new Exception\RuntimeException('Cannot insert row');
        }

        $id = $this->getMessageTable()->getLastInsertValue();
        $this->embedMessageInfo($queue, $message, $id, $params);

        return $message;
    }

    /**
     * Get messages from the queue
     *
     * @param  Queue $queue
     * @param  integer|null $maxMessages Maximum number of messages to return
     * @param  ReceiveParameters $params
     * @return MessageIterator
     * @throws Exception\QueueNotFoundException
     * @throws Exception\RuntimeException - database error
     */
    public function receiveMessages(Queue $queue, $maxMessages = null, ReceiveParameters $params = null)
    {
        if ($maxMessages === null) {
            $maxMessages = 1;
        }

        $timeout = $params ? $params->getVisibilityTimeout() : null;
        $filter = $params ? $params->getClassFilter() : null;
        $peek = $params ? $params->getPeekMode() : false;
        $msgs = array();
        $microtime = (int)microtime(true); // cache microtime
        $connection = $this->adapter->getDriver()->getConnection();
        $messageTable = $this->getMessageTable();

        // start transaction handling
        try {
            if ($maxMessages > 0) {
                $connection->beginTransaction();

                $sql = $messageTable->getSql();
                $select = $sql->select();

                $where = array(
                    'queue_id' => $this->getQueueId($queue->getName()),
                    '(schedule IS NULL OR schedule <= ?)' => $microtime,
                    '(handle IS NULL OR timeout < ' . $microtime . ')',
                );

                if ($filter) {
                    $where['class'] = (string)$filter;
                }

                $select->where($where);
                $select->limit($maxMessages);

                $select->getSqlString($this->adapter->getPlatform());

                $results = $this->getMessageTable()->selectWith($select)->toArray();

                foreach ($results as $message) {

                    $message['handle'] = md5(uniqid(rand(), true));
                    $message['timeout'] = $microtime;

                    $keepMessage = true;

                    if (!$peek) {
                        $update = $sql->update();
                        $update->set(array('handle' => $message['handle'], 'timeout' => $timeout ? $timeout + $microtime : null));
                        $update->where(array('message_id' => $message['message_id']));
                        $update->where($where);

                        $affectedRows = $messageTable->updateWith($update);

                        // we check count to make sure no other thread has gotten
                        // the rows after our select, but before our update.
                        if ($affectedRows < 1) {
                            $keepMessage = false;
                        }
                    }

                    if ($keepMessage) {
                        $message['metadata'] = isset($message['metadata']) ? unserialize($message['metadata']) : array();
                        $message['metadata'][$queue->getOptions()->getMessageMetadatumKey()] = $this->buildMessageInfo(
                            $message['handle'],
                            (int)$message['message_id'],
                            $queue,
                            array(
                                SendParameters::SCHEDULE => $message['schedule'],
                                SendParameters::REPEATING_INTERVAL => $message['interval'],
                            )
                        );

                        $msgs[] = array(
                            'class'     => $message['class'],
                            'content'   => $message['content'],
                            'metadata'    => $message['metadata'],
                        );
                    }

                }
                $connection->commit();
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }


        $classname = $queue->getOptions()->getMessageSetClass();
        return new $classname($msgs, $queue);
    }

    /**
     * Delete a message from the queue
     *
     * Return true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * @param  Queue $queue
     * @param  MessageInterface $message
     * @return boolean
     * @throws Exception\QueueNotFoundException
     * @throws Exception\RuntimeException - database error
     */
    public function deleteMessage(Queue $queue, MessageInterface $message)
    {
        $info = $this->getMessageInfo($queue, $message);
        $queueId = $this->getQueueId($queue->getName());

        if (isset($info['messageId'])) {
            $where = array('message_id' => $info['messageId'], 'queue_id' => $queueId);

            if ($info['handle']) {
                $where['handle'] = $info['handle'];
            } else {
                $where[] = 'handle IS NULL';
            }

            if (!empty($info['options'][SendParameters::REPEATING_INTERVAL])) {
                $microtime = (int) microtime(true);
                $result = $this->getMessageTable()->update(array(
                    'schedule' => $microtime + $info['options'][SendParameters::REPEATING_INTERVAL], 'handle' => null, 'timeout' => null
                ), $where);
            } else {
                $result = $this->getMessageTable()->delete($where);
            }

            if ($result) {
                $this->cleanMessageInfo($queue, $message);
                return true;
            }
        }
        return false;
    }

}
