yii-amqp
========
AMQP extension wrapper to communicate with RabbitMQ server. Based on **videlalvaro/php-amqplib**

**Requirements: PHP 5.3** due to the use of `namespaces`.

#How to use#

Yii congif file:
```php
'components' => [
	'amqp' => ['class' => 'application.extensions.yii-amqp.CAMQP'],
	...
```
or
```php
'components' => [
	'amqp' => [
		'class' => 'application.extensions.yii-amqp.CAMQP',
		'host' => '127.0.0.1',
		'port' => '5672',
		'login'=>'quest',
		'password'=>'quest',
		'vhost'=>'/',
	],
	...
```

#Hello World example

##Yii controller (publisher) with fanout/direct/topic:
```php
	Yii::app()->amqp->declareQueue('hello');
	Yii::app()->amqp->publish_message('Hello World', '', 'hello');
	Yii::app()->amqp->closeConnection();
```
##Consumer:
```php
/**
 * Class TestCommand
 * @property CAMQP $amqp
 */
 class TestCommand extends CConsoleCommand
 {
 	 /**
 	 * @var null
 	 */
 	private $amqp = null;
 	
 	public function __construct($name, $runner)
 	{
 		$this->amqp = Yii::app()->amqp;
 		parent::__construct($name, $runner);
	}
	
	public function actionListen()
	{
		$this->amqp->declareQueue('hello');
		$this->amqp->channel->basic_consume('hello', '', false, true, false, false, array($this, 'processOrder'));
		
		while(count($this->amqp->channel->callbacks)){
			$this->amqp->channel->wait();
		}
		
		$this->amqp->closeConnection();
	}

	 /**
	 * @param $msg
	 */
	public function processOrder($msg)
	{
		echo $msg->body.PHP_EOL;
	}
}
```
