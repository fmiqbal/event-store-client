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

namespace ProophTest\EventStoreClient\Security;

use function Amp\call;
use Generator;
use Prooph\EventStore\Common\SystemRoles;
use Prooph\EventStore\Exception\AccessDenied;
use Prooph\EventStore\Exception\NotAuthenticated;

class write_stream_meta_security extends AuthenticationTestCase
{
    /** @test */
    public function writing_meta_with_not_existing_credentials_is_not_authenticated(): Generator
    {
        yield $this->expectExceptionFromCallback(NotAuthenticated::class, fn () => $this->writeMeta('metawrite-stream', 'badlogin', 'badpass', 'user1'));
    }

    /** @test */
    public function writing_meta_to_stream_with_no_credentials_is_denied(): Generator
    {
        yield $this->expectExceptionFromCallback(AccessDenied::class, fn () => $this->writeMeta('metawrite-stream', null, null, 'user1'));
    }

    /** @test */
    public function writing_meta_to_stream_with_not_authorized_user_credentials_is_denied(): Generator
    {
        yield $this->expectExceptionFromCallback(AccessDenied::class, fn () => $this->writeMeta('metawrite-stream', 'user2', 'pa$$2', 'user1'));
    }

    /** @test */
    public function writing_meta_to_stream_with_authorized_user_credentials_succeeds(): Generator
    {
        yield $this->expectNoExceptionFromCallback(fn () => $this->writeMeta('metawrite-stream', 'user1', 'pa$$1', 'user1'));
    }

    /** @test */
    public function writing_meta_to_stream_with_admin_user_credentials_succeeds(): Generator
    {
        yield $this->expectNoExceptionFromCallback(fn () => $this->writeMeta('metawrite-stream', 'adm', 'admpa$$', 'user1'));
    }

    /** @test */
    public function writing_meta_to_no_acl_stream_succeeds_when_no_credentials_are_passed(): Generator
    {
        yield $this->expectNoExceptionFromCallback(fn () => $this->writeMeta('noacl-stream', null, null, null));
    }

    /** @test */
    public function writing_meta_to_no_acl_stream_is_not_authenticated_when_not_existing_credentials_are_passed(): Generator
    {
        yield $this->expectExceptionFromCallback(NotAuthenticated::class, fn () => $this->writeMeta('noacl-stream', 'badlogin', 'badpass', null));
    }

    /** @test */
    public function writing_meta_to_no_acl_stream_succeeds_when_any_existing_user_credentials_are_passed(): Generator
    {
        yield $this->expectNoExceptionFromCallback(fn () => call(function (): Generator {
            yield $this->writeMeta('noacl-stream', 'user1', 'pa$$1', null);
            yield $this->writeMeta('noacl-stream', 'user2', 'pa$$2', null);
        }));
    }

    /** @test */
    public function writing_meta_to_no_acl_stream_succeeds_when_admin_user_credentials_are_passed(): Generator
    {
        yield $this->expectNoExceptionFromCallback(fn () => $this->writeMeta('noacl-stream', 'adm', 'admpa$$', null));
    }

    /** @test */
    public function writing_meta_to_all_access_normal_stream_succeeds_when_no_credentials_are_passed(): Generator
    {
        yield $this->expectNoExceptionFromCallback(fn () => $this->writeMeta('normal-all', null, null, SystemRoles::ALL));
    }

    /** @test */
    public function writing_meta_to_all_access_normal_stream_is_not_authenticated_when_not_existing_credentials_are_passed(): Generator
    {
        yield $this->expectExceptionFromCallback(NotAuthenticated::class, fn () => $this->writeMeta('normal-all', 'badlogin', 'badpass', SystemRoles::ALL));
    }

    /** @test */
    public function writing_meta_to_all_access_normal_stream_succeeds_when_any_existing_user_credentials_are_passed(): Generator
    {
        yield $this->expectNoExceptionFromCallback(fn () => call(function (): Generator {
            yield $this->writeMeta('normal-all', 'user1', 'pa$$1', SystemRoles::ALL);
            yield $this->writeMeta('normal-all', 'user2', 'pa$$2', SystemRoles::ALL);
        }));
    }

    /** @test */
    public function writing_meta_to_all_access_normal_stream_succeeds_when_admin_user_credentials_are_passed(): Generator
    {
        yield $this->expectNoExceptionFromCallback(fn () => $this->writeMeta('normal-all', 'adm', 'admpa$$', SystemRoles::ALL));
    }
}
