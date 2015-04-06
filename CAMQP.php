<?php

/**
 * Class CAMQP
 * AMQP extension wrapper to communicate with RabbitMQ server
 *
 * @property \PhpAmqpLib\Connection\AMQPConnection $connect This Connect loading
 * @property \PhpAmqpLib\Channel\AMQPChannel $channel This Channel loading
 */
class CAMQP extends CApplicationComponent
{

	/**
	 * @var string
	 */
	public $host = '127.0.0.1';

	/**
	 * @var string
	 */
	public $port = '5672';

	/**
	 * @var string
	 */
	public $login = 'guest';

	/**
	 * @var string
	 */
	public $password = 'guest';

	/**
	 * @var string
	 */
	public $vhost = '/';

	/**
	 * @var \PhpAmqpLib\Connection\AMQPConnection
	 */
	private $_connect = null;

	/**
	 * @var \PhpAmqpLib\Channel\AMQPChannel
	 */
	private $_channel = null;

	/**
	 * Connect loading
	 * @return \PhpAmqpLib\Connection\AMQPConnection
	 */
	public function getConnect()
	{
		if($this->_connect === null){
			$this->_connect = new \PhpAmqpLib\Connection\AMQPConnection(
				$this->host, $this->port, $this->login, $this->password, $this->vhost);
		}

		return $this->_connect;
	}

	/**
	 * Chanel loading
	 * @return \PhpAmqpLib\Channel\AMQPChannel
	 */
	public function getChannel()
	{
		if($this->_channel === null){
			$this->_channel = $this->getConnect()->channel();
		}

		return $this->_channel;
	}


	public function init()
	{
		parent::init();
		Yii::setPathOfAlias('PhpAmqpLib', __DIR__.'/vendor/videlalvaro/php-amqplib/PhpAmqpLib');
	}


	/**
	 * @param $name $exchange
	 * @param string $type (direct)
	 * @param bool $passive (false)
	 * @param bool $durable (true) the exchange will survive server restarts
	 * @param bool $auto_delete (false) the exchange won't be deleted once the channel is closed.
	 * @return mixed
	 */
	public function declareExchange($name, $type = 'fanout', $passive = false, $durable = true, $auto_delete = false)
	{
		return $this->getChannel()->exchange_declare($name, $type, $passive, $durable, $auto_delete);
	}


	/**
	 * @param $name
	 * @param bool $passive
	 * @param bool $durable the queue will survive server restarts
	 * @param bool $exclusive the queue can be accessed in other channels
	 * @param bool $auto_delete (false) the queue won't be deleted once the channel is closed.
	 * @return mixed
	 */
	public function declareQueue($name, $passive = false, $durable = false, $exclusive = false, $auto_delete = false)
	{
		return $this->getChannel()->queue_declare($name, $passive, $durable, $exclusive, $auto_delete);
	}

	/**
	 * @param $queueName
	 * @param $exchangeName
	 * @param string $routingKey
	 */
	public function bindQueueExchanger($queueName, $exchangeName, $routingKey = '')
	{
		$this->getChannel()->queue_bind($queueName, $exchangeName, $routingKey);
	}

	/**
	 * @param $message
	 * @param $exchangeName
	 * @param string $routingKey
	 * @param string $content_type
	 * @param string $app_id
	 */
	public function publish_message($message, $exchangeName, $routingKey = '', $content_type = 'text/plain', $app_id = '')
	{
		$toSend = new PhpAmqpLib\Message\AMQPMessage($message,
			array(
				'content_type' => $content_type,
				'content_encoding' => 'utf-8',
				'app_id' => $app_id,
				'delivery_mode' => 2
			));

		$this->getChannel()->basic_publish($toSend, $exchangeName, $routingKey);
	}

	public function closeConnection()
	{
		$this->getChannel()->close();
		$this->getConnect()->close();
	}

	public function exchangeDelete($name)
	{
		$this->getChannel()->exchange_delete($name);
	}

}
