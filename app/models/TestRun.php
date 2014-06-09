<?php

use Magniloquent\Magniloquent\Magniloquent;

class TestRun extends Magniloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'test_runs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('id', 'name', 'description', 'test_plan_id');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('');

	protected static $rules = array(
		"save" => array(
				'name' => 'required|min:2',
				'description' => '',
				'test_plan_id' => 'required'
		),
		"create" => array(
				'name' => 'required|min:2',
				'description' => '',
				'test_plan_id' => 'required'
		),
		"update" => array(
				'name' => 'required|min:2',
				'description' => '',
				'test_plan_id' => 'required'
		)
	);

	protected static $relationships = array(
		'testplan' => array('belongsTo', 'TestPlan', 'test_plan_id'),
		'executions' => array('hasMany', 'Execution', 'test_run_id')
	);

	public function countTestCases()
	{
		return static::join('test_plans', 'test_plans.id', '=', 'test_runs.test_plan_id')
			->join('test_plans_test_cases', 'test_plans_test_cases.test_plan_id', '=', 'test_plans.id')
			->count('test_plans_test_cases.test_case_version_id');
	}

	protected static $purgeable = [''];

	public function progress()
	{
		$percentage = 0;
		$progress = array();
		$total = $this->countTestCases();
		$executions = Execution::select('executions.*')
			->where('executions.test_run_id', $this->id)
			->groupBy('test_case_version_id')->get();

		$executionStatuses = ExecutionStatus::all();
		$executionStatusesCount = array();
		foreach ($executionStatuses as $executionStatus)
		{
			$executionStatusesCount[$executionStatus->id] = 0;
		}
		foreach ($executions as $execution)
		{
			$executionStatusesCount[$execution->execution_status_id] += 1;
		}
		$executionStatusesCount[1] = count($executionStatusesCount) - $total;
		foreach ($executionStatusesCount as $statusId => $count)
		{
			$progress[$statusId] = ($count / $total) * 100;
		}
		$percentage = ($executions->count()/$total) * 100;
		return array(
			'percentage' => $percentage,
			'progress' => $progress
		);
	}

	// public function testcases()
	// {
	// 	return $this->belongsToMany('TestCase2', 'test_plans_test_cases', 'test_plan_id', 'test_case_id')
	// 			->withTimestamps();
	// }

}