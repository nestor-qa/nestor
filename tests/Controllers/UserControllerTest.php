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

namespace Controllers;

use \TestCase;
use \Mockery;
use \Hash;
use Nestor\Entities\User;
use Nestor\Repositories\UsersRepository;
use Nestor\Http\Controllers\UsersController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class UserControllerTest extends TestCase
{

    use DatabaseTransactions;

    public function testCreateUser() {
        $payload = [
            'username' => 'mariah',
            'name' => 'Mariah', 
            'email' => 'hsifuh#@llsad.ii.com',
            'password' => '123abc'
        ];

        $dispatcher = $this->app->make('Dingo\Api\Dispatcher');

        $response = $dispatcher->post('auth/signup', $payload);

        $this->assertEquals($payload['username'], $response['username']);
        $this->assertEquals($payload['name'], $response['name']);
        $this->assertEquals($payload['email'], $response['email']);
        $this->assertTrue($response['id'] > 0);
        $this->assertTrue(isset($response['created_at']));
        $this->assertTrue(isset($response['updated_at']));
        $this->assertFalse(isset($response['password']));
    }

    public function testCreateUserValidator() {
        $payload = [
            'username' => 'mariah',
            'name' => 'Mariah', 
            //'email' => 'hsifuh#@llsad.ii.com',
            'password' => '123abc'
        ];

        $dispatcher = $this->app->make('Dingo\Api\Dispatcher');

        $this->setExpectedException('Dingo\Api\Exception\InternalHttpException');
        $dispatcher->post('auth/signup', $payload);
    }

    public function testLogout() {
        $dispatcher = $this->app->make('Dingo\Api\Dispatcher');

        $response = $dispatcher->get('auth/logout');
        $this->assertTrue(isset($response['success']));
        $this->assertEquals("User successfully logged out.", $response['success']);
    }

    public function testLogin() {
        $payload = [
            'username' => 'mariah',
            'name' => 'Mariah', 
            'email' => 'hsifuh#@llsad.ii.com',
            'password' => '123abc'
        ];

        $dispatcher = $this->app->make('Dingo\Api\Dispatcher');

        $response = $dispatcher->post('auth/signup', $payload);

        $this->assertEquals($payload['username'], $response['username']);
        $this->assertEquals($payload['name'], $response['name']);
        $this->assertEquals($payload['email'], $response['email']);
        $this->assertTrue($response['id'] > 0);
        $this->assertTrue(isset($response['created_at']));
        $this->assertTrue(isset($response['updated_at']));
        $this->assertFalse(isset($response['password']));

        $loginPayload = [
            'username' => 'mariah',
            'password' => '123abc'
        ];

        $response = $dispatcher->post('auth/login', $loginPayload);
        $this->assertEquals($payload['username'], $response['username']);
        $this->assertEquals($payload['name'], $response['name']);
        $this->assertEquals($payload['email'], $response['email']);
        $this->assertTrue($response['id'] > 0);
        $this->assertTrue(isset($response['created_at']));
        $this->assertTrue(isset($response['updated_at']));
        $this->assertFalse(isset($response['password']));
    }

    // public function testCheckLogin() {
    //     $dispatcher = $this->app->make('Dingo\Api\Dispatcher');

    //     $user = $dispatcher->get('auth');
    //     $this->assertNull($user);

    //     $payload = [
    //         'username' => 'mariah',
    //         'name' => 'Mariah', 
    //         'email' => 'hsifuh#@llsad.ii.com',
    //         'password' => '123abc'
    //     ];

    //     $response = $dispatcher->post('auth/signup', $payload);

    //     $this->assertEquals($payload['username'], $response['username']);
    //     $this->assertEquals($payload['name'], $response['name']);
    //     $this->assertEquals($payload['email'], $response['email']);
    //     $this->assertTrue($response['id'] > 0);
    //     $this->assertTrue(isset($response['created_at']));
    //     $this->assertTrue(isset($response['updated_at']));
    //     $this->assertFalse(isset($response['password']));
    // }

}
