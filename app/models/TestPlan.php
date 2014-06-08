<?php

use Magniloquent\Magniloquent\Magniloquent;

class TestPlan extends Magniloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'test_plans';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('id', 'name', 'description', 'project_id');

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
			'project_id' => 'required'
		),
		"create" => array(
			'name' => 'unique:test_plans,name,project_id,:project_id|required|min:2',
			'description' => '',
			'project_id' => 'required'
		),
		"update" => array()
	);

	protected static $relationships = array(
		'project' => array('belongsTo', 'Project', 'project_id'),
		'testruns' => array('hasMany', 'TestRun')
	);

	protected static $purgeable = [''];

	public function testcasesDetached()
	{
		$sql = <<<EOF
select tc.*, tcv.version 
from test_cases tc 
inner join test_case_versions tcv on tc.id = tcv.test_case_id 
inner join test_plans_test_cases tptc on tptc.test_case_version_id = tcv.id 
where tptc.test_plan_id = :test_plan_id 
group by tc.id 
EOF;
		$results = DB::select(DB::raw($sql), array('test_plan_id' => $this->id));
		return $results;
	}

	public function hasExecutions()
	{
		return TestPlan::join('test_runs', 'test_runs.test_plan_id', '=', $this->id)
			->join('executions', 'executions.test_run_id', '=', 'test_runs.id')
			->count('test_plans.id') > 0;
	}

}