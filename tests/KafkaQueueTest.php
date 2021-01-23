<?php

use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Rapide\LaravelQueueKafka\Queue\Jobs\KafkaJob;
use Rapide\LaravelQueueKafka\Queue\KafkaQueue;

/**
 * @property array config
 */
class KafkaQueueTest extends TestCase
{
    protected $producer;
    protected $consumer;
    protected $contianer;
    protected $topic;
    protected $queue;
    protected $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->producer = Mockery::mock(\RdKafka\Producer::class);
        $this->consumer = Mockery::mock(\RdKafka\Consumer::class);
        $this->container = Mockery::mock(\Illuminate\Container\Container::class);
        $this->topic = Mockery::mock(\RdKafka\Topic::class);
        $this->kafkaQueue = Mockery::mock(\RdKafka\Queue::class);

        $this->config = [
            'queue' => Str::random(),
            'sleep_error' => true,
        ];

        $this->queue = new KafkaQueue($this->producer, $this->consumer, $this->config);
        $this->queue->setContainer($this->container);
    }

    public function test_size()
    {
        $size = $this->queue->size();
        $messageCount = 1;

        $this->assertEquals($messageCount, $size);
    }

    public function test_push()
    {
        $job = new TestJob();
        $data = [];

        $this->topic->shouldReceive('produce');

        $this->producer->shouldReceive('newTopic')->andReturn($this->topic);
        $this->producer->shouldReceive('poll');

        $correlationId = $this->queue->push($job, $data);

        $this->assertEquals(23, strlen($correlationId));
    }

    public function test_later()
    {
        $delay = 5;
        $job = new TestJob();

        $this->expectException(\Exception::class);

        $this->queue->later($delay, $job);
    }

    public function test_pop_no_error()
    {
        $queue = $this->config['queue'];
        $message = Mockery::mock(\RdKafka\Message::class);
        $message->err = RD_KAFKA_RESP_ERR_NO_ERROR;

        $this->producer->shouldReceive('newTopic')->andReturn($this->topic);

        $this->consumer->shouldReceive('subscribe')->with([$queue]);
        $this->consumer->shouldReceive('consume')->andReturn($message);
        $this->consumer->shouldReceive('newQueue')->andReturn($this->kafkaQueue);
        $this->consumer->shouldReceive('newTopic')->andReturn($this->topic);

        $this->topic->shouldReceive('consumeQueueStart');

        $this->kafkaQueue->shouldReceive('consume')->andReturn(new \RdKafka\Message());

        $job = $this->queue->pop($queue);

        $this->assertInstanceOf(KafkaJob::class, $job);
    }

    public function test_pop_end_of_partition()
    {
        $queue = $this->config['queue'];
        $message = Mockery::mock(\RdKafka\Message::class);
        $message->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;

        $this->producer->shouldReceive('newTopic')->andReturn($this->topic);

        $this->consumer->shouldReceive('subscribe')->with([$queue]);
        $this->consumer->shouldReceive('consume')->andReturn($message);
        $this->consumer->shouldReceive('newQueue')->andReturn($this->kafkaQueue);
        $this->consumer->shouldReceive('newTopic')->andReturn($this->topic);

        $this->topic->shouldReceive('consumeQueueStart');
        $this->kafkaQueue->shouldReceive('consume');

        $job = $this->queue->pop($queue);

        $this->assertNull($job);
    }

    public function test_pop_timed_out()
    {
        $queue = $this->config['queue'];
        $message = Mockery::mock(\RdKafka\Message::class);
        $message->err = RD_KAFKA_RESP_ERR__TIMED_OUT;

        $this->producer->shouldReceive('newTopic')->andReturn($this->topic);

        $this->consumer->shouldReceive('subscribe')->with([$queue]);
        $this->consumer->shouldReceive('consume')->andReturn($message);
        $this->consumer->shouldReceive('newQueue')->andReturn($this->kafkaQueue);
        $this->consumer->shouldReceive('newTopic')->andReturn($this->topic);

        $this->topic->shouldReceive('consumeQueueStart');
        $this->kafkaQueue->shouldReceive('consume');

        $job = $this->queue->pop($queue);

        $this->assertNull($job);
    }

    public function test_pop_not_catched_exception()
    {
        $queue = $this->config['queue'];
        $message = Mockery::mock(\RdKafka\Message::class);
        $message->err = RD_KAFKA_RESP_ERR__ALL_BROKERS_DOWN;
        $message->shouldReceive('errstr');

        $this->consumer->shouldReceive('subscribe')->with([$queue]);
        $this->consumer->shouldReceive('consume')->andReturn($message);

        $this->expectException(\Exception::class);

        $this->queue->pop($queue);
    }

    public function test_setCorrelationId()
    {
        $id = Str::random();

        $this->queue->setCorrelationId($id);

        $setId = $this->queue->getCorrelationId();

        $this->assertEquals($id, $setId);
    }

    public function test_getConsumer()
    {
        $consumer = $this->queue->getConsumer();

        $this->assertEquals($consumer, $this->consumer);
    }
}
