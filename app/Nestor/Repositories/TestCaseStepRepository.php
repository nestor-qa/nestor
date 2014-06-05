<?php namespace Nestor\Repositories;

interface TestCaseStepRepository {

	/**
	 * Get all test case steps
	 *
	 * @return TestCaseStep
	 */
	public function all();

	/**
	 * Get a TestCaseStep by their primary key.
	 *
	 * @param  int   $id
	 * @return TestCaseStep
	 */
	public function find($id);

	/**
	 * Get a TestCaseStep by their test case id.
	 *
	 * @param  int   $test_case_id
	 * @return TestCaseStep
	 */
	public function findByTestCaseId($test_case_version_id);

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
	public function create($test_case_version_id, $order, $description, $expected_result, $execution_status_id);

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
	public function update($id, $test_case_version_id, $order, $description, $expected_result, $execution_status_id);

	/**
	 * Delete a test case step
	 *
	 * @param int $id
	 */
	public function delete($id);

}