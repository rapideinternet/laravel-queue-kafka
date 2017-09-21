<?php

namespace Rapide\LaravelQueueKafka\Queue\Connectors;

use Illuminate\Container\Container;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Rapide\LaravelQueueKafka\Queue\KafkaQueue;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;
use RdKafka\TopicConf;

class KafkaConnector implements ConnectorInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * KafkaConnector constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        /** @var Producer $producer */
        $producer = $this->container->makeWith('queue.kafka.producer', []);
        $producer->addBrokers($config['brokers']);

        /** @var TopicConf $topicConf */
        $topicConf = $this->container->makeWith('queue.kafka.topic_conf', []);
        $topicConf->set('auto.offset.reset', 'largest');

        /** @var Conf $conf */
        $conf = $this->container->makeWith('queue.kafka.conf', []);
        $conf->set('group.id', array_get($config, 'consumer_group_id', 'php-pubsub'));
        $conf->set('metadata.broker.list', $config['brokers']);
        $conf->set('enable.auto.commit', 'false');
        $conf->set('offset.store.method', 'broker');
        $conf->setDefaultTopicConf($topicConf);

        /** @var KafkaConsumer $consumer */
        $consumer = $this->container->makeWith('queue.kafka.consumer', ['conf' => $conf]);

        return new KafkaQueue(
            $producer,
            $consumer,
            $config
        );
    }
}
