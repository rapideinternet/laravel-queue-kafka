<?php

namespace Rapide\LaravelQueueKafka;

class LumenQueueKafkaServiceProvider extends LaravelQueueKafkaServiceProvider
{
    /**
     * Register the application's event listeners.
     */
    public function boot()
    {
        parent::boot();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/kafka.php', 'queue.connections.kafka'
        );
    }
}
