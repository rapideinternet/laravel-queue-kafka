#!/bin/bash

set -e

cd /tmp \
    && mkdir librdkafka \
    && cd librdkafka \
    && git clone https://github.com/edenhill/librdkafka.git . \
    && ./configure \
    && make \
    && sudo make install

pecl install rdkafka

echo "extension=rdkafka.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini