<?php

use Magniloquent\Magniloquent\Magniloquent;

class Execution extends Magniloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'executions';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('id', 'test_run_id', 'test_case_version_id', 'execution_status_id', 'notes');

	protected static $relationships = array(
		'executionStatus' => array('belongsTo', 'ExecutionStatus', 'execution_status_id')
	);

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('');

	protected static $rules = array(
		"save" => array(
				'test_run_id' => 'required|numeric',
				'test_case_version_id' => 'required|numeric',
				'execution_status_id' => 'required|numeric',
				'notes' => ''
		),
		"create" => array(
		),
		"update" => array(
		)
	);

}