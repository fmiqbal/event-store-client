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

namespace ProophTest\EventStoreClient\UserManagement;

use Generator;
use Prooph\EventStore\Exception\InvalidArgumentException;
use Prooph\EventStore\Exception\UserCommandFailed;
use Prooph\EventStore\Transport\Http\HttpStatusCode;
use Prooph\EventStore\UserCredentials;
use ProophTest\EventStoreClient\DefaultData;

class change_password extends TestWithUser
{
    /** @test */
    public function empty_username_throws(): Generator
    {
        $this->expectException(InvalidArgumentException::class);

        yield $this->manager->changePasswordAsync('', 'oldPassword', 'newPassword', DefaultData::adminCredentials());
    }

    /** @test */
    public function empty_current_password_throws(): Generator
    {
        $this->expectException(InvalidArgumentException::class);

        yield $this->manager->changePasswordAsync($this->username, '', 'newPassword', DefaultData::adminCredentials());
    }

    /** @test */
    public function empty_new_password_throws(): Generator
    {
        $this->expectException(InvalidArgumentException::class);

        yield $this->manager->changePasswordAsync($this->username, 'oldPassword', '', DefaultData::adminCredentials());
    }

    /** @test */
    public function can_change_password(): Generator
    {
        yield $this->manager->changePasswordAsync(
            $this->username,
            'password',
            'fubar',
            new UserCredentials($this->username, 'password')
        );

        $this->expectException(UserCommandFailed::class);

        try {
            yield $this->manager->changePasswordAsync(
                $this->username,
                'password',
                'foobar',
                new UserCredentials($this->username, 'password')
            );
        } catch (UserCommandFailed $e) {
            $this->assertSame(HttpStatusCode::UNAUTHORIZED, $e->httpStatusCode());

            throw $e;
        }
    }
}
