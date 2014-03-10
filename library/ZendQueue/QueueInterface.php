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

/**
 *
 */
interface QueueInterface
{
    /**
     * Get the queue name
     *
     * @return string
     */
    public function getName();

    /**
     * Get options
     *
     * @return QueueOptions
     */
    public function getOptions();
}