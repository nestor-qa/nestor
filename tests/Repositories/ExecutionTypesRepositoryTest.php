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
use \Mockery;
use \Hash;
use Nestor\Entities\ExecutionTypes;
use Nestor\Repositories\ExecutionTypesRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExecutionTypesRepositoryTest extends TestCase
{

    use DatabaseTransactions;

    public function testRepositoryModelClass() {
        $repository = $this->app->make('Nestor\Repositories\ExecutionTypesRepository');
        $this->assertEquals(ExecutionTypes::class, $repository->model());
    }

    public function testCreateExecutionType() {
        $payload = [
            'name' => 'Musical Test', 
            'description' => 'A beautiful and lovely music, that describes a test'
        ];

        $executionTypesRepository = $this->mock(Nestor\Repositories\ExecutionTypesRepository::class);
        $executionTypesRepository
            ->shouldReceive('create')
            ->with(Mockery::any())
            ->once()
            ->andReturn(factory(ExecutionTypes::class)->make($payload));
        $executionType = $executionTypesRepository->create($payload);

        $this->assertEquals('Musical Test', $executionType['name']);
        $this->assertEquals('A beautiful and lovely music, that describes a test', $executionType['description']);
        $this->assertTrue($executionType['id'] > 0);
    }

}