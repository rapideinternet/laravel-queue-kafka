<?php

/**
 * This is an example of queue connection configuration.
 * It will be merged into config/queue.php.
 * You need to set proper values in `.env`
 */
return [

    'driver' => 'kafka',

    /*
     * The name of default queue.
     */
    'queue' => env('KAFKA_QUEUE', 'test1'),


    'consumer_group_id' => 'php-pubsub',
    'brokers' => env('KAFKA_BROKERS', 'localhost'),

    /*
     * Determine the number of seconds to sleep if there's an error communicating with kafka
     * If set to false, it'll throw an exception rather than doing the sleep for X seconds.
     */
    'sleep_on_error' => env('RABBITMQ_ERROR_SLEEP', 5),

];
