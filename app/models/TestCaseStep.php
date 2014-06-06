<?php

use Magniloquent\Magniloquent\Magniloquent;

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
	protected $fillable = array('id');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('');

	protected static $rules = array(
		"save" => array(
		),
		"create" => array(
		),
		"update" => array(
		)
	);

	protected static $relationships = array(
		'testCaseStepVersions' => array('hasMany', 'TestCaseStepVersion', 'test_case_step_id')
	);

	protected static $purgeable = [''];
	
}