<?php

/**
 * This file is part of `prooph/event-store-client`.
 * (c) 2018-2022 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2018-2022 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventStoreClient;

use Amp\Loop;
use Prooph\EventStore\EndPoint;
use Prooph\EventStore\EventData;
use Prooph\EventStore\EventId;
use Prooph\EventStore\ExpectedVersion;
use Prooph\EventStore\Position;
use Prooph\EventStore\StreamMetadata;
use Prooph\EventStore\UserCredentials;

require __DIR__ . '/../vendor/autoload.php';

Loop::run(function () {
    $builder = new ConnectionSettingsBuilder();
    $builder->enableVerboseLogging();
    $builder->useConsoleLogger();

    $connection = EventStoreConnectionFactory::createFromEndPoint(
        new EndPoint('eventstore', 1113),
        $builder->build()
    );

    $connection->onConnected(function (): void {
        echo 'connected' . PHP_EOL;
    });

    $connection->onClosed(function (): void {
        echo 'connection closed' . PHP_EOL;
    });

    yield $connection->connectAsync();

    $slice = yield $connection->readStreamEventsForwardAsync(
        'foo-bar',
        10,
        2,
        true
    );

    \var_dump(\get_class($slice));

    $slice = yield $connection->readStreamEventsBackwardAsync(
        'foo-bar',
        10,
        2,
        true
    );

    \var_dump(\get_class($slice));

    $event = yield $connection->readEventAsync('foo-bar', 2, true);

    \var_dump(\get_class($event));

    $m = yield $connection->getStreamMetadataAsync('foo-bar');

    \var_dump(\get_class($m));

    $r = yield $connection->setStreamMetadataAsync('foo-bar', ExpectedVersion::Any, new StreamMetadata(
        null,
        null,
        null,
        null,
        null,
        [
            'foo' => 'bar',
        ]
    ));

    \var_dump(\get_class($r));

    $m = yield $connection->getStreamMetadataAsync('foo-bar');

    \var_dump(\get_class($m));

    $wr = yield $connection->appendToStreamAsync('foo-bar', ExpectedVersion::Any, [
        new EventData(EventId::generate(), 'test-type', false, 'jfkhksdfhsds', 'meta'),
        new EventData(EventId::generate(), 'test-type2', false, 'kldjfls', 'meta'),
        new EventData(EventId::generate(), 'test-type3', false, 'aaa', 'meta'),
        new EventData(EventId::generate(), 'test-type4', false, 'bbb', 'meta'),
    ]);

    \var_dump(\get_class($wr));

    $ae = yield $connection->readAllEventsForwardAsync(Position::start(), 2, false, new UserCredentials(
        'admin',
        'changeit'
    ));

    \var_dump(\get_class($ae));

    $aeb = yield $connection->readAllEventsBackwardAsync(Position::end(), 2, false, new UserCredentials(
        'admin',
        'changeit'
    ));

    \var_dump(\get_class($aeb));

    $connection->close();
});
