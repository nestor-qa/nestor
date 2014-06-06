<?php namespace Nestor\Repositories;

use Auth, Hash, Validator;
use TestCase2;
use DB;
use Log;
use TestCaseVersion;

class DbTestCaseRepository implements TestCaseRepository {

	/**
	 * Get all of the test cases.
	 *
	 * @return array
	 */
	public function all()
	{
		return TestCase2::all();
	}

	/**
	 * Get a TestCase by their primary key.
	 *
	 * @param  int   $id
	 * @return TestCase
	 */
	public function find($id)
	{
		return TestCase2::
			with(array('testCaseVersions' => function($query) 
			{
				$query->orderBy('version', 'desc');
			}))
			->with('testCaseVersions.executionType')
			->findOrFail($id);
	}

	/**
	 * Create a test case
	 *
	 * @param  int     $project_id
	 * @param  int     $test_suite_id
	 * @param  int     $execution_type_id
	 * @param  string  $name
	 * @param  string  $description
	 * @return array(TestCase, TestCaseVersion)
	 */
	public function create($project_id, $test_suite_id, $execution_type_id, $name, $description, $prerequisite)
	{
		Log::debug('Creating new test case');
		$pdo = DB::connection()->getPdo();
		$testcase = TestCase2::create(compact('project_id', 'test_suite_id'));
		$test_case_id = $pdo->lastInsertId();
		$testcase->id = $test_case_id;
		$version = 1;
		Log::debug(sprintf('Creating initial test case version for test case %d', $testcase->id));
		$testcaseVersion = TestCaseVersion::create(compact('version', 'test_case_id', 'execution_type_id', 'name', 'description', 'prerequisite'));
		Log::debug('Version 1 created');
		return array($testcase, $testcaseVersion);
	}

	/**
	 * Update a test case
	 *
	 * @param  int     $project_id
	 * @param  int     $test_suite_id
	 * @param  int     $execution_type_id
	 * @param  string  $name
	 * @param  string  $description
	 * @return array(TestCase, TestCaseVersion)
	 */
	public function update($id, $project_id, $test_suite_id, $execution_type_id, $name, $description, $prerequisite)
	{
		$testcase = $this->find($id);
		$test_case_id = $testcase->id;

		Log::debug('Retrieving previous version');
		$previousVersion = $testcase->latestVersion();

		$version = $previousVersion->version;
		Log::debug(sprintf('Updating test case version for test case %d', $testcase->id));
		$version += 1;

		Log::debug(sprintf('Creating version %d for test case %d', $version, $testcase->id));
		$testcaseVersion = TestCaseVersion::create(compact('version', 'test_case_id', 'execution_type_id', 'name', 'description', 'prerequisite'));

		Log::debug(sprintf('Version %d created', $version));
		return array($testcase, $testcaseVersion);
	}

	/**
	 * Delete a test case
	 * @param int $id
	 */
	public function delete($id)
	{
		return TestCase2::where('id', $id)->delete();
	}

	public function isNameAvailable($id, $test_suite_id, $name)
	{
		return TestCase2::select('test_cases.*, test_case_versions.name')
			->join('test_case_versions', 'test_cases.id', '=', 'test_case_versions.id')
			->where('test_cases.id', '<>', $id)
			->where('test_suite_id', '=', $test_suite_id)
			->where(new \Illuminate\Database\Query\Expression("lower(test_case_versions.name)"), '=', strtolower($name))
			->count() == 0;
	}

}
