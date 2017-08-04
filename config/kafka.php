<?php

/**
 * This is an example of queue connection configuration.
 * It will be merged into config/queue.php.
 * You need to set proper values in `.env`.
 */
return [
    /*
     * Driver name
     */
    'driver' => 'kafka',

    /*
     * The name of default queue.
     */
    'queue' => env('KAFKA_QUEUE', 'default'),

    /*
     * The group of where the consumer in resides.
     */
    'consumer_group_id' => env('KAFKA_CONSUMER_GROUP_ID', 'laravel_queue'),

    /*
     * Address of the Kafka broker
     */
    'brokers' => env('KAFKA_BROKERS', 'localhost'),

    /*
     * Determine the number of seconds to sleep if there's an error communicating with kafka
     * If set to false, it'll throw an exception rather than doing the sleep for X seconds.
     */
    'sleep_on_error' => env('KAFKA_ERROR_SLEEP', 5),

];
