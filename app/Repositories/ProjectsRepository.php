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

namespace Nestor\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface ProjectsRepository
 * @package namespace Nestor\Repositories;
 */
interface ProjectsRepository extends RepositoryInterface
{

    /**
     * Return an array with the test cases versions IDs that have been executed in a
     * certain project.
     * @param integer $projectId project ID
     * @return array with the TestCaseVersions that have been executed. i.e. return all
     * test cases versions that have at least one occurrence in the executions table.
     */
    public function getExecutedTestCaseVersionIds($projectId);

    /**
     * Create simple project report.
     *
     * @param int $projectId
     * @return mixed Array
     */
    public function createSimpleProjectReport($projectId);
}
