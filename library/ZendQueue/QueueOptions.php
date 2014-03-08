<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueue;

use Zend\Stdlib\AbstractOptions;

class QueueOptions extends AbstractOptions
{

    /**
     * @var array
     */
    protected $driverOptions = array();

    /**
     * @var array
     */
    protected $adapterOptions = array();


    /**
     * The default message class
     *
     * @var string
     */
    protected $messageClass = '\ZendQueue\Message\Message';

    /**
     * default message set (iterator) class
     *
     * @var string
     */
    protected $messageSetClass = '\ZendQueue\Message\MessageIterator';

    /**
     * Metadata key name used to inject queue info into message
     *
     * @var string
     */
    protected $messageMetadatumKey = '__queue';


    /**
     * When await capability isn't available,
     * if this flag is true polling will be used instead,
     * else an exception will be trown
     *
     * @var bool
     */
    protected $enableAwaitEmulation = true;

    /**
     * Used only if enableAwaitEmulation is true
     *
     * @var int
     */
    protected $pollingInterval = 1;

    /**
     * @param string $class
     * @return QueueOptions
     */
    public function setMessageClass($class)
    {
        $this->messageClass = (string)$class;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageClass()
    {
        return $this->messageClass;
    }

    /**
     * @param string $class
     * @return QueueOptions
     */
    public function setMessageSetClass($class)
    {
        $this->messageSetClass = (string)$class;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageSetClass()
    {
        return $this->messageSetClass;
    }

    /**
     * Set the key name used to embed queue info into message's metadata
     *
     * @param string $keyName
     * @return QueueOptions
     */
    public function setMessageMetadatumKey($keyName)
    {
        $this->messageMetadatumKey = (string)$keyName;
        return $this;
    }

    /**
     * Get the key name used to embed queue info into message's metadata
     *
     * @return string
     */
    public function getMessageMetadatumKey()
    {
        return $this->messageMetadatumKey;
    }

    /**
     * @param bool $flag
     * @return \ZendQueue\QueueOptions
     */
    public function setEnableAwaitEmulation($flag)
    {
        $this->enableAwaitEmulation = (bool)$flag;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableAwaitEmulation()
    {
        return $this->enableAwaitEmulation;
    }

    /**
     * @param int $sec
     * @return QueueOptions
     */
    public function setPollingInterval($sec)
    {
        $this->pollingInterval = (int)$sec;
        return $this;
    }

    /**
     * @return int
     */
    public function getPollingInterval()
    {
        return $this->pollingInterval;
    }

}