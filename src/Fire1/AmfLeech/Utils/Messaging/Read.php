<?php
/**
 * Created by PhpStorm.
 * Author: Angel Zaprianov <me@fire1.eu>
 * Date: 3/22/16
 * Time: 10:23 AM
 */

namespace Fire1\AmfLeech\Utils\Messaging;

use Fire1\AmfLeech\Curl\SendRequest;


/**
 * Class Read
 * @package Fire1\AmfLeech\Utils\Messaging
 */
class Read
{

    public static $clientChanelId = null;
    /** External static configuration
     * @type bool
     */
    public static $change_msg_id = true;
    /**
     * @type \stdClass
     */
    protected $data;
    /**
     * @type \stdClass
     */
//    protected $header;

    /**
     * @param     $messages
     * @param int $msg_position
     * @param int $data_position
     */
    public function __construct( &$messages, $msg_position = 0, $data_position = 0 )
    {
        $message = $messages->messages[ $msg_position ];
        $this->data = is_array($message->data) ? $message->data[ $data_position ] : $message->data;

        !self::$change_msg_id ?: $this->generateIds();
    }


    /**
     * @return array|\stdClass
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Handle communication chanel with server
     * @use SessionEvent
     * @param     $value
     * @param int $version
     */
    public function setHeaders( $value = null, $version = 1 )
    {
        $this->data->headers = new Head($value, $version);
    }

    /**
     * @return null|object
     */
    public function getHeaders()
    {
        return ( isset( $this->data->headers ) ) ? $this->data->headers : null;
    }

    /** Gets message ID
     * @return string
     */
    public function getMsgId()
    {
        return $this->getMessageId();
    }

    /**
     * @return null|string
     */
    public function getMessageId()
    {
        switch (true):
            case isset( $this->getData()->messageId ) :
                return $this->getData()->messageId;
            default:
                return null;

        endswitch;
    }


    /**
     * @return string|null
     */
    public function getChanelId()
    {
        switch (true):
            case  isset( $this->getData()->_externalizedData->DSId ):
                return $this->getData()->_externalizedData->DSId;

            case isset( $this->getData()->headers ) && isset( $this->getData()->headers->DSId ):
                return $this->getData()->headers->DSId;

            default:
                return SendRequest::$chanel;

        endswitch;
    }

    /**
     * Gets Body object from AMF message
     * @param int $index
     * @return object|null
     */
    public function getBody( $index = 0 )
    {
        switch (true):
            case is_array($this->getData()->body) && $index > 0:
                return $this->getData()->body[ $index ];
            case is_array($this->getData()->body) && $index == 0:
                return current($this->getData()->body);
            case is_object($this->getData()->body):
                return $this->getData()->body;

            default:
            case is_null($this->getData()->body):
                return null;
        endswitch;
    }

    /**
     * @param $name
     */
    public function __get( $name )
    {
        return $this->getBody()->{$name};
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set( $name, $value )
    {
        return $this->getBody()->{$name} = $value;
    }

    /**
     *  Regenerate inner message ID
     * @return Read
     */
    public function generateIds()
    {
        !empty( SendRequest::$chanel ) ?:
            self::$clientChanelId = SendRequest::$chanel = $this->getRandomId();

        $this->setHeaders();
        $this->getData()->messageId = $this->getRandomId();
        return $this;
    }

    /**
     * Generate random ID
     * @return string
     */
    public function getRandomId()
    {
        // version 4 UUID
        return sprintf(
            '%08X-%04X-%04X-%02X%02X-%012X', mt_rand(), mt_rand(0, 65535), bindec(substr_replace(
                sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)
        ), bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)), mt_rand(0, 255), mt_rand()
        );
    }

}