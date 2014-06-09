<?php namespace Nestor\Repositories;

use Execution;

class DbExecutionRepository implements ExecutionRepository {

	public function all()
	{
		return Execution::all();
	}

	public function find($id)
	{
		return Execution::find($id);
	}

	public function findByTestRunId($test_run_id)
	{
		return Execution::where('test_run_id', $test_run_id)->get();
	}

	public function findByTestCaseVersionId($testCaseVersionId)
	{
		return Execution::where('test_case_version_id', $testCaseVersionId)->get();
	}

	public function findByExecutionStatusId($execution_status_id)
	{
		return Execution::where('execution_status_id', $execution_status_id)->get();
	}

	public function create($test_run_id, $test_case_version_id, $execution_status_id, $notes)
	{
		return Execution::create(compact('test_run_id', 'test_case_version_id', 'execution_status_id', 'notes'));
	}

	public function update($id, $test_run_id, $test_case_version_id, $execution_status_id, $notes)
	{
		$execution = $this->find($id);

		$execution->fill(compact('execution', 'test_case_version_id', 'execution_status_id', 'notes'))->save();

		return $execution;
	}

	public function delete($id)
	{
		return Execution::where('id', $id)->delete();
	}

	public function getExecutionsForTestCaseVersion($testCaseVersionId, $testRunId)
	{
		return Execution::where('test_case_version_id', '=', $testCaseVersionId)
			->where('test_run_id', '=', $testRunId)
			->orderBy('executions.created_at');
	}

}