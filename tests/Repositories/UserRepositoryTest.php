<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Bruno P. Kinoshita, Peter Florijn
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Repositories;

use \TestCase;
use Nestor\Entities\User;
use Nestor\Repositories\UsersRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserRepositoryTest extends TestCase
{

    use DatabaseTransactions;

    public function testRepositoryModelClass()
    {
        $repository = $this->app->make(\Nestor\Repositories\UsersRepository::class);
        $this->assertEquals(User::class, $repository->model());
    }

    public function testCreateUser()
    {
        $payload = [
            'username' => $this->faker->uuid,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->md5
        ];
        $payload['password'] = bcrypt($payload['password']);

        $usersRepository = app()->make(\Nestor\Repositories\UsersRepository::class);
        $object = $usersRepository->create($payload);

        $this->assertTrue($object['id'] > 0);
        foreach ($payload as $key => $value) {
            $this->assertEquals($payload[$key], $object[$key]);
        }
    }

    /**
     * Test that a user name is case insensitive in the system. Otherwise users may start filling
     * bugs complaining that they are not able to log in, and we may get accidental wrong user names too.
     *
     * @see https://github.com/nestor-qa/nestor/issues/96
     */
    public function testCreateUserIsCaseInsensitive()
    {
        $dispatcher = $this->app->make('Dingo\Api\Dispatcher');
        $payload = [
            'username' => ucfirst($this->faker->word) . $this->faker->uuid,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->md5
        ];
        $originalPassword = $payload['password'];
        $payload['password'] = bcrypt($payload['password']);

        $usersRepository = app()->make(\Nestor\Repositories\UsersRepository::class);
        $object = $usersRepository->create($payload);
        $this->assertTrue($object['id'] > 0);

        $loweCaseUserName = strtolower($payload['username']);

        $loginPayload = [
            'username' => $loweCaseUserName,
            'password' => $originalPassword
        ];

        $response = $dispatcher->post('auth/login', $loginPayload);
        $this->assertTrue($response['id'] > 0);
    }
}
