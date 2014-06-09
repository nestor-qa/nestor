<?php

use Magniloquent\Magniloquent\Magniloquent;

class TestCase2 extends Magniloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'test_cases';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('id', 'test_suite_id', 'project_id');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('');

	protected static $purgeable = [];

	protected static $rules = array(
		"save" => array(
			'test_suite_id' => 'required',
			'project_id' => 'required'
		),
		"create" => array(
		),
		"update" => array(
		),
	);

	protected static $relationships = array();

	public function project()
	{
		return $this->belongsTo('Project', 'project_id');
	}

	public function testSuite()
	{
		return $this->belongsTo('TestSuite', 'test_suite_id');
	}

	public function testCaseVersions()
	{
		return $this->hasMany('TestCaseVersion', 'test_case_id');
	}

	public function latestVersion()
	{
		return $this->hasMany('TestCaseVersion', 'test_case_id')
			->orderBy('version', 'desc')
			->take(1)
			->firstOrFail();
	}

	public function steps()
	{
		return $this->hasManyThrough('TestCaseStepVersion', 'TestCaseVersion', 'test_case_id', 'test_case_version_id');
	}

}