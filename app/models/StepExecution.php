<?php

use Magniloquent\Magniloquent\Magniloquent;

class StepExecution extends Magniloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'step_executions';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('id', 'execution_id', 'test_case_step_version_id', 'execution_status_id');

	protected static $relationships = array(
		'executionStatus' => array('belongsTo', 'ExecutionStatus', 'execution_status_id'),
		'testCaseStepVersion' => array('belongsTo', 'TestCaseStepVersion', 'test_case_step_version_id')
	);

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('');

	protected static $rules = array(
		"save" => array(
				'execution_id' => 'required|numeric',
				'test_case_step_version_id' => 'required|numeric',
				'execution_status_id' => 'required|numeric'
		),
		"create" => array(
		),
		"update" => array(
		)
	);

}