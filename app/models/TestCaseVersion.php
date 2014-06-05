<?php

use Magniloquent\Magniloquent\Magniloquent;
use \Execution;

class TestCaseVersion extends Magniloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'test_case_versions';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('id', 'version', 'name', 'description', 'prerequisite', 'test_case_id', 'execution_type_id');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('');

	protected static $rules = array(
		"save" => array(
			'version' => 'required|numeric|min:1',
			'name' => 'required|min:2',
			'description' => '',
			'prerequisite' => '',
			'test_case_id' => 'required|numeric',
			'execution_type_id' => 'required'
		),
		"create" => array(
			'version' => 'required|numeric|min:1',
			'name' => 'required|min:2',
			'description' => '',
			'prerequisite' => '',
			'test_case_id' => 'required|numeric',
			'execution_type_id' => 'required'
		),
		"update" => array(
			'version' => 'required|numeric|min:1',
			'name' => 'required|min:2',
			'description' => '',
			'prerequisite' => '',
			'test_case_id' => 'required|numeric',
			'execution_type_id' => 'required'
		),
	);

	protected static $relationships = array(
		'testcase' => array('belongsTo', 'TestCase2', 'test_case_id'),
		'executionType' => array('belongsTo', 'ExecutionType', 'execution_type_id'),
		'executions' => array('hasMany', 'Execution', 'test_case_id'),
		'steps' => array('hasMany', 'TestCaseStep', 'test_case_version_id')
	);

	protected static $purgeable = [''];
	
	public function sortedSteps()
	{
		return TestCaseVersion::hasMany('TestCaseStep', 'test_case_version_id')->orderBy('order')->get();
	}

}