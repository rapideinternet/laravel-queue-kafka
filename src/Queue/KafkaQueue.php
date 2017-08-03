<?php

namespace Rapide\LaravelQueueKafka\Queue;

use ErrorException;
use Exception;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;
use Log;
use Rapide\LaravelQueueKafka\Queue\Jobs\KafkaJob;

class KafkaQueue extends Queue implements QueueContract
{
    protected $defaultQueue;
    protected $sleepOnError;

    /**
     * @var int
     */
    private $retryAfter;

    /**
     * @var string
     */
    private $correlationId;
    /**
     * @var \RdKafka\Producer
     */
    private $producer;
    /**
     * @var \RdKafka\KafkaConsumer
     */
    private $consumer;

    /**
     * @param \RdKafka\Producer $producer
     * @param \RdKafka\KafkaConsumer $consumer
     * @param array $config
     */
    public function __construct(\RdKafka\Producer $producer, \RdKafka\KafkaConsumer $consumer, $config)
    {

        $this->defaultQueue = $config['queue'];
        $this->sleepOnError = isset($config['sleep_on_error']) ? $config['sleep_on_error'] : 5;

        $this->producer = $producer;
        $this->consumer = $consumer;
    }

    /**
     * Get the size of the queue.
     *
     * @param string $queue
     *
     * @return int
     */
    public function size($queue = null)
    {
        $meta = $this->consumer->getMetadata(false, $this->getTopic($queue), 60e3);

        return 1;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param string $job
     * @param mixed $data
     * @param string $queue
     *
     * @return bool
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue, []);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param string $payload
     * @param string $queue
     * @param array $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        try {
            $topic = $this->getTopic($queue);
            $correlationId = $this->getCorrelationId();
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $payload, $correlationId);

            return $correlationId;
        } catch (ErrorException $exception) {
            throw $exception;
            $this->reportConnectionError('pushRaw', $exception);
        }
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTime|int $delay
     * @param string $job
     * @param mixed $data
     * @param string $queue
     *
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        throw new Exception('Later not yet implemented');
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string|null $queue
     *
     * @return \Illuminate\Queue\Jobs\Job|null
     */
    public function pop($queue = null)
    {
        $this->consumer->subscribe([$this->getQueueName($queue)]);

        $message = $this->consumer->consume(30 * 1000);

        if ($message === null) {
            return;
        }

        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                return new KafkaJob($this->container, $this, $message, $queue ?: $this->defaultQueue);

                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                break;
            default:
                throw new \Exception($message->errstr(), $message->err);
        }
    }

    /**
     * @param string $queue
     *
     * @return string
     */
    private function getQueueName($queue)
    {
        return $queue ?? $this->defaultQueue;
    }

    private function getTopic($queue)
    {
        return $this->producer->newTopic($this->getQueueName($queue));
    }

    /**
     * Sets the attempts member variable to be used in message generation.
     *
     * @param int $count
     *
     * @return void
     */
    public function setAttempts($count)
    {
        $this->retryAfter = $count;
    }

    /**
     * Sets the correlation id for a message to be published.
     *
     * @param string $id
     *
     * @return void
     */
    public function setCorrelationId($id)
    {
        $this->correlationId = $id;
    }

    /**
     * Retrieves the correlation id, or a unique id.
     *
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->correlationId ?: uniqid('', true);
    }

    /**
     * @param string $action
     * @param Exception $e
     * @throws Exception
     */
    protected function reportConnectionError($action, Exception $e)
    {
        Log::error('Kafka error while attempting ' . $action . ': ' . $e->getMessage());

        // If it's set to false, throw an error rather than waiting
        if ($this->sleepOnError === false) {
            throw new \RuntimeException('Error writing data to the connection with Kafka');
        }

        // Sleep so that we don't flood the log file
        sleep($this->sleepOnError);
    }

    /**
     * @return \RdKafka\KafkaConsumer
     */
    public function getConsumer(): \RdKafka\KafkaConsumer
    {
        return $this->consumer;
    }
}
