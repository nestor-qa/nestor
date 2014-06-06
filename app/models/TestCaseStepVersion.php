<?php

use Magniloquent\Magniloquent\Magniloquent;

class TestCaseStepVersion extends Magniloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'test_case_step_versions';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('id', 'version', 'test_case_version_id', 'test_case_step_id', 'order', 'description', 'expected_result', 'execution_status_id');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('');

	protected static $rules = array(
		"save" => array(
				'version' => 'required|numeric|min:1',
				'test_case_version_id' => 'required|numeric',
				'test_case_step_id' => 'required|numeric',
				'order' => 'required|numeric',
				'description' => '',
				'expected_result' => '',
				'execution_status_id' => 'required|numeric'
		),
		"create" => array(
		),
		"update" => array(
		)
	);

	protected static $relationships = array(
		'testCaseVersion' => array('belongsTo', 'TestCaseVersion', 'test_case_version_id'),
		'testCaseStep' => array('belongsTo', 'TestCaseStep', 'test_case_step_id'),
		'executionStatus' => array('belongsTo', 'ExecutionStatus', 'execution_status_id'),
		'executions' => array('hasMany', 'StepExecution', 'test_case_step_id')
	);

	protected static $purgeable = [''];
	
}