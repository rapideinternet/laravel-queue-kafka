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
    'brokers' => env('KAFKA_BROKERS', '127.0.0.1:9092'),

    /*
     * Determine the number of seconds to sleep if there's an error communicating with kafka
     * If set to false, it'll throw an exception rather than doing the sleep for X seconds.
     */
    'sleep_on_error' => env('KAFKA_ERROR_SLEEP', 5),

    /*
     * Sleep when a deadlock is detected
     */
    'sleep_on_deadlock' => env('KAFKA_DEADLOCK_SLEEP', 2),

    /*
     * sasl authorization
     */
    'sasl_enable' => false,

    /*
     * File or directory path to CA certificate(s) for verifying the broker's key. example: storage_path('kafka.client.truststore.jks')
     */
    'ssl_ca_location' => '',

    /*
     * SASL username for use with the PLAIN and SASL-SCRAM-.. mechanisms
     */
    'sasl_plain_username' => env('KAFKA_SASL_PLAIN_USERNAME'),

    /*
     * SASL password for use with the PLAIN and SASL-SCRAM-.. mechanism
     */
    'sasl_plain_password' => env('KAFKA_SASL_PLAIN_PASSWORD'),
];
