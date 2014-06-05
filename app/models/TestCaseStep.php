<?php

use Magniloquent\Magniloquent\Magniloquent;
use \Execution;

class TestCaseStep extends Magniloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'test_case_steps';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('id', 'test_case_version_id', 'order', 'description', 'expected_result', 'execution_status_id');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('');

	protected static $rules = array(
		"save" => array(
				'description' => '',
				'test_case_version_id' => 'required|numeric',
				'order' => 'required|numeric',
				'expected_result' => '',
				'execution_status_id' => 'required|numeric'
		),
		"create" => array(
				'description' => '',
				'test_case_version_id' => 'required|numeric',
				'order' => 'required|numeric',
				'expected_result' => '',
				'execution_status_id' => 'required|numeric'
		),
		"update" => array(
				'description' => '',
				'test_case_version_id' => 'required|numeric',
				'order' => 'required|numeric',
				'expected_result' => '',
				'execution_status_id' => 'required|numeric'
		)
	);

	protected static $relationships = array(
		'executionStatus' => array('belongsTo', 'ExecutionStatus', 'execution_status_id'),
		'executions' => array('hasMany', 'StepExecution', 'test_case_step_id')
	);

	protected static $purgeable = [''];
	
}