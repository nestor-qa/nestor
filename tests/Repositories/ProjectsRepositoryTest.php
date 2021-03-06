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
use Nestor\Entities\Projects;
use Nestor\Repositories\ProjectsRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectsRepositoryTest extends TestCase
{

    use DatabaseTransactions;

    public function testRepositoryModelClass()
    {
        $projectsRepository = $this->app->make(\Nestor\Repositories\ProjectsRepository::class);
        $this->assertEquals(Projects::class, $projectsRepository->model());
    }

    public function testCreateProject()
    {
        $payload = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence(3),
            'created_by' => $this->faker->word,
            'project_statuses_id' => $this->faker->numberBetween(1, 1000)
        ];

        $projectRepository = app()->make(\Nestor\Repositories\ProjectsRepository::class);
        $project = $projectRepository->create($payload);

        $this->assertTrue($project['id'] > 0);
        foreach ($payload as $key => $value) {
            $this->assertEquals($payload[$key], $project[$key]);
        }
    }

    public function testUpdateProject()
    {
        $payload = [
            'name' => $this->faker->uuid,
            'description' => $this->faker->sentence(3),
            'created_by' => $this->faker->word,
            'project_statuses_id' => $this->faker->numberBetween(1, 1000)
        ];

        $projectRepository = app()->make(\Nestor\Repositories\ProjectsRepository::class);
        $project = $projectRepository->create($payload);

        $this->assertTrue($project['id'] > 0);
        
        $payload['name'] = 'Updated name';

        $projectUpdated = $projectRepository->update($payload, $project['id']);

        foreach ($payload as $key => $value) {
            if (strcmp("name", $key) !== 0) {
                $this->assertEquals($payload[$key], $projectUpdated[$key]);
            } else {
                $this->assertEquals('Updated name', $projectUpdated['name']);
            }
        }
    }

    public function testDeleteProject()
    {
        $payload = [
            'name' => $this->faker->uuid,
            'description' => $this->faker->sentence(3),
            'created_by' => $this->faker->word,
            'project_statuses_id' => $this->faker->numberBetween(1, 1000)
        ];

        $projectRepository = app()->make(\Nestor\Repositories\ProjectsRepository::class);
        $project = $projectRepository->create($payload);

        $this->assertTrue($project['id'] > 0);
        
        $r = $projectRepository->delete($project['id']);

        $this->assertTrue($r > 0);
    }

    public function testGetExecutedTestCaseVersionIds()
    {
        $projectPayload = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence(3),
            'created_by' => $this->faker->word,
            'project_statuses_id' => $this->faker->numberBetween(1, 1000)
        ];

        $projectRepository = app()->make(\Nestor\Repositories\ProjectsRepository::class);
        $project = $projectRepository->create($projectPayload);

        $testPlanPayload = [
            'project_id' => $project['id'],
            'name' => $this->faker->sentence(2),
            'description' => $this->faker->sentence(10)
        ];

        $testPlanRepository = app()->make(\Nestor\Repositories\TestPlansRepository::class);
        $testPlan = $testPlanRepository->create($testPlanPayload);

        $testRunPayload = [
            'test_plan_id' => $testPlan['id'],
            'name' => $this->faker->sentence(2),
            'description' => $this->faker->sentence(10)
        ];

        $testRunRepository = app()->make(\Nestor\Repositories\TestRunsRepository::class);
        $testRun = $testRunRepository->create($testRunPayload);

        $testCasePayload = [
            'project_id' => $this->faker->numberBetween(1, 1000),
            'test_suite_id' => $this->faker->numberBetween(1, 1000)
        ];

        $testCaseVersionPayload = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence(3),
            'prerequisite' => $this->faker->sentence(5),
            'version' => 1,
            'execution_type_id' => $this->faker->numberBetween(1, 5)
        ];

        $testCaseRepository = app()->make(\Nestor\Repositories\TestCasesRepository::class);
        $testCase = $testCaseRepository->createWithAncestor($testCasePayload, $testCaseVersionPayload, '2-1');

        $executionsPayload = [
            'test_run_id' => $testRun['id'],
            'test_cases_versions_id' => $testCase->latestVersion()['id'],
            'execution_status_id' => 1,
            'notes' => $this->faker->sentence(10)
        ];

        $executionsRepository = app()->make(\Nestor\Repositories\ExecutionsRepository::class);

        $execution = $executionsRepository->execute(
            $executionsPayload['execution_status_id'],
            $executionsPayload['notes'],
            $executionsPayload['test_run_id'],
            $executionsPayload['test_cases_versions_id']
        );

        $ids = $projectRepository->getExecutedTestCaseVersionIds($project['id'])->toArray();

        $this->assertEquals([$testCase['id']], $ids);
    }
}
