<?php namespace Nestor\Repositories;

use Auth, Hash, Validator;
use TestCaseStep;
use TestCaseStepVersion;
use Log;
use DB;

class DbTestCaseStepRepository implements TestCaseStepRepository {

	/**
	 * Get all test case steps
	 *
	 * @return TestCaseStep
	 */
	public function all()
	{
		return TestCaseStep::all();
	}

	/**
	 * Get a TestCaseStep by their primary key.
	 *
	 * @param  int   $id
	 * @return TestCaseStep
	 */
	public function find($id)
	{
		Log::debug(sprintf('Retrieving test case step %d', $id));
		return TestCaseStep::
			with(array('testCaseStepVersions' => function($query) 
			{
				$query->orderBy('version', 'desc');
			}))
			->with('testCaseStepVersions.executionStatus')
			->findOrFail($id);
	}

	/**
	 * Get a TestCaseStep by their test case id.
	 *
	 * @param  int   $testCaseVersionId
	 * @return TestCaseStep
	 */
	public function findByTestCaseVersionId($testCaseVersionId)
	{
		return TestCaseStep::where('test_case_version_id', '=', $testCaseVersionId)->all();
	}

	/**
	 * Create a test case step
	 *
	 * @param  int     $test_case_version_id
	 * @param  int     $order
	 * @param  string  $description
	 * @param  int     $expected_result
	 * @param  int  $execution_status_id
	 * @return TestCaseStep
	 */
	public function create($test_case_version_id, $order, $description, $expected_result, $execution_status_id)
	{
		Log::debug('Creating new test case step');
		$pdo = DB::connection()->getPdo();
		$testcaseStep = TestCaseStep::create(array());
		$test_case_step_id = $pdo->lastInsertId();
		$testcaseStep->id = $test_case_step_id;
		$version = 1;
		Log::debug(sprintf('Creating initial test case step version for test case step %d', $testcaseStep->id));
		$testcaseStepVersion = TestCaseStepVersion::create(compact('version', 'test_case_version_id', 'test_case_step_id', 'order', 'description', 'expected_result', 'execution_status_id'));
		Log::debug('Version 1 created');
		return array($testcaseStep, $testcaseStepVersion);
	}

	/**
	 * Update a test case step
	 *
	 * @param  int     $id
	 * @param  int     $test_case_version_id
	 * @param  int     $order
	 * @param  string  $description
	 * @param  int     $expected_result
	 * @param  int  $execution_status_id
	 * @return TestCaseStep
	 */
	public function update($id, $test_case_version_id, $order, $description, $expected_result, $execution_status_id)
	{
		$testcaseStep = $this->find($id);
		$test_case_step_id = $testcaseStep->id;

		Log::debug('Retrieving previous version');
		$previousVersion = $testcaseStep->testCaseStepVersions->first();

		$version = $previousVersion->version;
		Log::debug(sprintf('Updating test case step version for test case step %d', $testcaseStep->id));
		$version += 1;

		Log::debug(sprintf('Creating version %d for test case step %d', $version, $testcaseStep->id));
		$testcaseStepVersion = TestCaseStepVersion::create(compact('version', 'test_case_version_id', 'test_case_step_id', 'order', 'description', 'expected_result', 'execution_status_id'));

		Log::debug(sprintf('Version %d created', $version));
		return array($testcaseStep, $testcaseStepVersion);
	}

	/**
	 * Delete a test case step
	 *
	 * @param int $id
	 */
	public function delete($id)
	{
		return TestCaseStep::where('id', $id)->delete();
	}

}
