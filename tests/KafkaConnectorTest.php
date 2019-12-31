<?php

use PHPUnit\Framework\TestCase;
use Rapide\LaravelQueueKafka\Queue\Connectors\KafkaConnector;
use Rapide\LaravelQueueKafka\Queue\KafkaQueue;

class KafkaConnectorTest extends TestCase
{
    public function test_connect()
    {
        $config = [
            'host' => getenv('HOST'),
            'port' => getenv('PORT'),
            'queue' => 'queue_name',
            'consumer_group_id' => 'php-pubsub',
            'brokers' => 'localhost',
            'sleep_on_error' => 5,
        ];

        $container = Mockery::mock(\Illuminate\Container\Container::class);

        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);

        $topic_conf = Mockery::mock(\RdKafka\TopicConf::class);
        $topic_conf->shouldReceive('set');

        $producer = Mockery::mock(\RdKafka\Producer::class);
        $producer->shouldReceive('addBrokers')->withArgs([$config['brokers']]);

        $conf = Mockery::mock(\RdKafka\Conf::class);
        $conf->shouldReceive('set')->atLeast(4);
        $conf->shouldReceive('setDefaultTopicConf')->with($topic_conf);

        $container
            ->shouldReceive('makeWith')
            ->withArgs(['queue.kafka.producer', []])
            ->andReturn($producer);
        $container
            ->shouldReceive('makeWith')
            ->withArgs(['queue.kafka.topic_conf', []])
            ->andReturn($topic_conf);
        $container
            ->shouldReceive('makeWith')
            ->withArgs(['queue.kafka.consumer', ['conf' => $conf]])
            ->andReturn($consumer);
        $container
            ->shouldReceive('makeWith')
            ->withArgs(['queue.kafka.conf', []])
            ->andReturn($conf);

        $connector = new KafkaConnector($container);
        $queue = $connector->connect($config);

        $this->assertInstanceOf(KafkaQueue::class, $queue);
    }
}
