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
use Nestor\Entities\ExecutionTypes;
use Nestor\Repositories\ExecutionTypesRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExecutionTypesRepositoryTest extends TestCase
{

    use DatabaseTransactions;

    public function testRepositoryModelClass()
    {
        $repository = $this->app->make(\Nestor\Repositories\ExecutionTypesRepository::class);
        $this->assertEquals(ExecutionTypes::class, $repository->model());
    }

    public function testCreateExecutionType()
    {
        $payload = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence(2)
        ];

        $executionTypesRepository = $this->app->make(\Nestor\Repositories\ExecutionTypesRepository::class);
        $executionType = $executionTypesRepository->create($payload);

        $this->assertTrue($executionType['id'] > 0);
        foreach ($payload as $key => $value) {
            $this->assertEquals($payload[$key], $executionType[$key]);
        }
    }
}
