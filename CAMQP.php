<?php
/**
 * Class CAMQP
 * AMQP extension wrapper to communicate with RabbitMQ server
 */
class CAMQP extends CApplicationComponent
{

	public $vhost;

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
	 * @var null
	 */
	private $_connect;

	/**
	 * @var null
	 */
	private $_channel;


	public function init()
	{
		parent::init();
		Yii::setPathOfAlias('PhpAmqpLib', __DIR__.'/vendor/videlalvaro/php-amqplib/PhpAmqpLib');
		$this->_connect = new PhpAmqpLib\Connection\AMQPConnection($this->host, $this->port, $this->login, $this->password);
		$this->_channel = $this->_connect->channel();
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

		return $this->_channel->exchange_declare($name, $type, $passive, $durable, $auto_delete);
	}


	/**
	 * @param $name
	 * @param bool $passive
	 * @param bool $durable  the queue will survive server restarts
	 * @param bool $exclusive  the queue can be accessed in other channels
	 * @param bool $auto_delete (false) the queue won't be deleted once the channel is closed.
	 * @return mixed
	 */
	public function declareQueue($name, $passive = false, $durable = false, $exclusive = false, $auto_delete = false)
	{
		return $this->_channel->queue_declare($name, $passive, $durable, $exclusive, $auto_delete);
	}

	/**
	 * @param $queueName
	 * @param $exchangeName
	 * @param string $routingKey
	 */
	public function bindQueueExchanger($queueName, $exchangeName, $routingKey = '')
	{
		$this->_channel->queue_bind($queueName, $exchangeName, $routingKey);
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

		$this->_channel->basic_publish($toSend, $exchangeName, $routingKey);

		//$msg = $this->_channel->basic_get('q1');
		//var_dump($msg);
	}

	public function closeConnection()
	{
		$this->_channel->close();
		$this->_connect->close();
	}

	public function exchangeDelete($name)
	{
		$this->_channel->exchange_delete($name);
	}

}
