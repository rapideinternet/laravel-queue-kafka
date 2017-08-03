Kafka Queue driver for Laravel
======================
[![Latest Stable Version](https://poser.pugx.org/rapide/laravel-queue-kafka/v/stable?format=flat-square)](https://packagist.org/packages/rapide/laravel-queue-kafka)
[![Build Status](https://img.shields.io/travis/rapide/laravel-queue-kafka.svg?style=flat-square)](https://travis-ci.org/rapide/laravel-queue-kafka)
[![Total Downloads](https://poser.pugx.org/rapide/laravel-queue-kafka/downloads?format=flat-square)](https://packagist.org/packages/rapide/laravel-queue-kafka)
[![StyleCI](https://styleci.io/repos/14976752/shield)](https://styleci.io/repos/14976752)
[![License](https://poser.pugx.org/rapide/laravel-queue-kafka/license?format=flat-square)](https://packagist.org/packages/rapide/laravel-queue-kafka)

#### Installation

1. Install this package via composer using:

	`composer require rapide/laravel-queue-kafka:5.4`

2. Add LaravelQueueKafkaServiceProvider to `providers` array in `config/app.php`:

	`Rapide\LaravelQueueKafka\LaravelQueueKafkaServiceProvider::class,`

3. Add these properties to `.env` with proper values:

		QUEUE_DRIVER=kafka


You can also find full examples in src/examples folder.

#### Usage

Once you completed the configuration you can use Laravel Queue API. If you used other queue drivers you do not need to change anything else. If you do not know how to use Queue API, please refer to the official Laravel documentation: http://laravel.com/docs/queues

#### Testing

Run the tests with:

``` bash
vendor/bin/phpunit
```


#### Contribution

You can contribute to this package by discovering bugs and opening issues. Please, add to which version of package you create pull request or issue. (e.g. [5.2] Fatal error on delayed job)

#### Supported versions of Laravel (+Lumen)

5.4`

The version is being matched by the release tag of this library.
