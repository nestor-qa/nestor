<?php namespace Nestor\Repositories;

interface ExecutionRepository {

	public function all();

	public function find($id);

	public function findByTestRunId($test_run_id);

	public function findByTestCaseVersionId($test_case_id);

	public function findByExecutionStatusId($execution_status_id);

	public function create($test_run_id, $test_case_id, $execution_status_id, $notes);

	public function update($id, $test_run_id, $test_case_id, $execution_status_id, $notes);

	public function delete($id);

	public function getExecutionsForTestCaseVersion($testCaseId, $testRunId);

}