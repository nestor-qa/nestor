<?php

use Nestor\Model\Nodes;
use Nestor\Util\NavigationTreeUtil;

use utilphp\util;

class TestRunsController extends NavigationTreeController 
{

	protected $theme;

	public $restful = true;

	public function __construct()
	{
		parent::__construct();
		$this->theme->setActive('execution');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$testPlanId = Input::get('test_plan_id');
		$testPlan = HMVC::get("api/v1/testplans/$testPlanId");
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Runs for Test Plan %s', $testPlan['name']));
		$testRuns = HMVC::get("api/v1/testplans/$testPlanId/testruns");
		$args = array();
		$args['testruns'] = $testRuns;
		$args['testplan'] = $testPlan;
		return $this->theme->scope('execution.testrun.index', $args)->render();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$testPlanId = Input::get('test_plan_id');
		$testPlan = HMVC::get("api/v1/testplans/$testPlanId");
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Create Test Run for Test Plan %s', $testPlan['name']));
		$args = array();
		$args['testplan'] = $testPlan;
		return $this->theme->scope('execution.create', $args)->render();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$args = array();
		$testrun = HMVC::get("api/v1/execution/testruns/$id");
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Run %s', $testrun['name']));
		$args['testrun'] = $testrun;
		$args['testplan'] = $testrun['testplan'];
		return $this->theme->scope('execution.testrun.show', $args)->render();
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$args = array();
		$testrun = HMVC::get("api/v1/execution/testruns/$id");
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Run %s', $testrun['name']));
		$args['testrun'] = $testrun;
		$args['testplan'] = $testrun['testplan'];
		return $this->theme->scope('execution.testrun.edit', $args)->render();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$testRun = HMVC::put("api/v1/executions/$id", Input::all());

		if (!$testRun || (isset($testRun['code']) && $testRun['code'] != 200)) {
			return Redirect::to(URL::previous())->withInput()->withErrors($testRun['description']);
		}

		return Redirect::to("/execution/testruns/$id")
			->with('success', sprintf('Test Run %s updated', $testRun['name']));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Log::info('Destroying test run...');
		$testrun = $this->testruns->find($id);
		$testplan = $testrun->testplan()->first();
		$this->testruns->delete($id);

		return Redirect::to('execution/testruns?test_plan_id=' . $testplan->id)
			->with('success', sprintf('The test run %s has been deleted', $testrun->name));
	}

	public function runGet($testRunId) 
	{
		Log::info(sprintf('Executing Test Run %d', $testRunId));
		$currentProject = $this->getCurrentProject();
		$testRun = HMVC::get("api/v1/execution/testruns/$testRunId");
		$testPlanId = $testRun['test_plan_id'];
		$testPlan = HMVC::get("api/v1/testplans/$testPlanId");
		$testCaseVersions = $testPlan['test_cases'];

		Log::debug('Creating breadcrumb');
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Runs for Test Plan %s', $testPlan['name']), URL::to(sprintf('/execution/testruns?test_plan_id=%d', $testPlan['id'])))->
			add(sprintf('Test Run %s', $testRun['name']));

		$filter = array(); // Our filter
		foreach ($testCaseVersions as $version)
		{
			$filter[$version['test_case_id']] = TRUE;
		}

		$nodeId = Nodes::id(Nodes::PROJECT_TYPE, $currentProject['id']);
		$nodes = HMVC::get("api/v1/nodes/$nodeId");

		// create a navigation tree
		$navigationTree = NavigationTreeUtil::createNavigationTree(
			$nodes, Nodes::id(Nodes::PROJECT_TYPE, $currentProject['id'])
		);

		// use it to create the HTML version
		$navigationTreeHtml = NavigationTreeUtil::createExecutionNavigationTreeHtml(
			$navigationTree, 
			NULL, 
			$this->theme->getThemeName(),
			array(), 
			$filter,
			$testRunId
		);

		$args = array();
		$args['testrun'] = $testRun;
		$args['testplan'] = $testPlan;
		$args['testcases'] = $testCaseVersions;
		$args['navigation_tree'] = $navigationTree;
		$args['navigation_tree_html'] = $navigationTreeHtml;
		$args['current_project'] = $this->currentProject;

		return $this->theme->scope('execution.testrun.run', $args)->render();
	}

	public function runTestCase($testRunId, $testCaseId) 
	{
		Log::info(sprintf('Executing Test Run %d, Test Case %d', $testRunId, $testCaseId));
		$currentProject = $this->getCurrentProject();
		$testRun = HMVC::get("api/v1/execution/testruns/$testRunId");
		$testPlanId = $testRun['test_plan_id'];
		$testPlan = HMVC::get("api/v1/testplans/$testPlanId");
		$testCaseVersions = $testPlan['test_cases'];
		$testCase = HMVC::get("api/v1/testcases/$testCaseId");
		$testCaseVersion = $testCase['version'];

		$executionStatuses = HMVC::get("api/v1/executionstatuses/");

		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Runs for Test Plan %s', $testPlan['name']), 
				URL::to(sprintf('/execution/testruns?test_plan_id=%d', $testPlan['id'])))->
			add(sprintf('Test Run %s', $testRun['name']));

		$showOnly = array(); // Our filter

		$assignee = null;
		foreach ($testCaseVersions as $testCaseVersion2)
		{
			$showOnly[$testCaseVersion2['test_case_id']] = $testCaseVersion2;
			if ($testCaseVersion2['id'] == $testCaseVersion['id'])
			{
				$assigneeId = isset($testCaseVersion2['assignee']) ?: null;
				if (is_null($assigneeId))
				{
					$assignee = "Not assigned";
				}
				else
				{
					$user = $this->users->find($assigneeId);
					$assignee = $user->fullname;
				}
			}
		}

		$filter = array(); // Our filter
		foreach ($testCaseVersions as $version)
		{
			$filter[$version['test_case_id']] = TRUE;
		}

		$nodeId = Nodes::id(Nodes::PROJECT_TYPE, $currentProject['id']);
		$nodes = HMVC::get("api/v1/nodes/$nodeId");

		// create a navigation tree
		$navigationTree = NavigationTreeUtil::createNavigationTree(
			$nodes, Nodes::id(Nodes::PROJECT_TYPE, $currentProject['id'])
		);

		// use it to create the HTML version
		$navigationTreeHtml = NavigationTreeUtil::createExecutionNavigationTreeHtml(
			$navigationTree, 
			NULL, 
			$this->theme->getThemeName(),
			array(), 
			$filter,
			$testRunId
		);

		$executions = HMVC::get("api/v1/execution/testruns/$testRunId/executions/${testCaseVersion['id']}");
		var_dump($executions);exit;

		$executions = $this->executions->getExecutionsForTestCaseVersion($testcaseVersion->id, $testRunId)->get();

		$lastExecution = $executions->last();
		$lastExecutionStatusId = 1; // FIXME magic number, 1 is NOT RUN
		if ($lastExecution != NULL)
		{
			$lastExecutionStatusId = $lastExecution->execution_status_id;
		}

		$steps = $testcase->steps()->get();
	
		foreach ($steps as $step)
		{
			if ($lastExecutionStatusId > 1)
			{
				$stepLastExecution = $this->stepExecutions->findByStepIdAndExecutionId($step->id, $lastExecution->id)->first();
				if ($stepLastExecution)
					$step->lastExecutionStatusId = $stepLastExecution->execution_status_id;
				else
					$step->lastExecutionStatusId = 1;
			}
			else
			{
				$step->lastExecutionStatusId = 1; // FIXME magic number
			}
		}

		$args = array();
		$args['testrun'] = $testRun;
		$args['testplan'] = $testPlan;
		$args['testcases'] = $testCases;
		$args['testcase'] = $testCase;
		$args['testcaseVersion'] = $testCaseVersion;
		$args['assignee'] = $assignee;
		$args['steps'] = $steps;
		$args['executions'] = $executions;
		$args['executionStatuses'] = $executionStatuses;
		$args['last_execution_status_id'] = $lastExecutionStatusId;
		$args['navigation_tree'] = $navigationTree;
		$args['navigation_tree_html'] = $navigationTreeHtml;
		$args['current_project'] = $this->currentProject;

		return $this->theme->scope('execution.testrun.runTestcase', $args)->render();
	}

	public function runTestCasePost($testRunId, $testCaseId) 
	{
		if (Input::get('execution_status_id') == 1) // FIXME use constants
		{
			Log::warning('Trying to set the test case execution status back to Not Run');
			$messages = new Illuminate\Support\MessageBag;
			$messages->add('nestor.customError', 'You cannot set an execution status back to Not Run');
			return Redirect::to(sprintf('/execution/testruns/%d/run/testcase/%d', $testRunId, $testCaseId))
				->withInput()
				->withErrors($messages);
		}
		$testcase = $this->testcases->find($testCaseId);
		$testcaseVersion = $testcase->latestVersion();
		$steps = $testcase->steps()->get();
		$stepResults = array();
		foreach ($_POST as $key => $value)
		{
			$matches = array();
			if (preg_match('^step_execution_status_id_(\d+)^', $key, $matches))
			{
				$stepResults[substr($key, strlen('step_execution_status_id_'))] = $value;
			}
		}
		if (count($stepResults) != $steps->count())
		{
			// Never supposed to happen
			Log::warning('Internal error. Wrong number of test steps execution statuses.');
			$messages = new Illuminate\Support\MessageBag;
			$messages->add('nestor.customError', 'Internal error. Wrong number of test steps execution statuses.');
			return Redirect::to(sprintf('/execution/testruns/%d/run/testcase/%d', $testRunId, $testCaseId))
				->withInput()
				->withErrors($messages);
		}
		foreach ($stepResults as $key => $value) 
		{
			if ($value == 1) // FIXME use constants
			{
				Log::warning('Trying to set the test case step execution status back to Not Run');
				$messages = new Illuminate\Support\MessageBag;
				$messages->add('nestor.customError', sprintf('You cannot set step %d execution status to Not Run', $key));
				return Redirect::to(sprintf('/execution/testruns/%d/run/testcase/%d', $testRunId, $testCaseId))
					->withInput()
					->withErrors($messages);
			}
		}

		Log::debug('Starting new DB transaction');
		DB::beginTransaction();

		try 
		{
			Log::debug('Retrieving test run');
			$testrun = $this->testruns->find($testRunId);
			Log::debug(sprintf('Creating a new execution for test case version %d with execution status %d', $testcaseVersion->id, Input::get('execution_status_id')));
			$execution = $this->executions->create($testrun->id, 
				$testcaseVersion->id, 
				Input::get('execution_status_id'), 
				Input::get('notes'));

			if ($execution->isValid() && $execution->isSaved())
			{
				// save its steps execution statuses
				foreach ($stepResults as $key => $value) 
				{
					Log::debug(sprintf('Creating new step execution for execution %d', $execution->id));
					$stepExecution = $this->stepExecutions->create($execution->id, $key, $value);
					if (!$stepExecution->isValid() || !$stepExecution->isSaved())
					{
						Log::error(var_export($stepExecution->errors(), TRUE));
						throw new Exception(sprintf("Failed to save step %d with execution status %d", $key, $value));
					}
				}
				Log::debug('Committing transaction');
				DB::commit();
				return Redirect::to(Request::url())->with('success', 'Test executed');
			} else {
				Log::error(var_export($execution->errors(), TRUE));
				throw new Exception(sprintf("Failed to save step %d with execution status %d", $key, $value));
			}
		} catch (Exception $e)
		{
			Log::debug('Rolling back transaction');
			DB::rollback();
			$messages = new Illuminate\Support\MessageBag;
			$messages->add('nestor.customError', $e->getMessage());
			return Redirect::to(sprintf('/execution/testruns/%d/run/testcase/%d', $testRunId, $testCaseId))
				->withInput()
				->withErrors($messages);
		}
	}

	public function getJUnit($testRunId)
	{
		Log::info(sprintf('Retrieving JUnit report for Test Run %d', $testRunId));
		$currentProject = $this->getCurrentProject();
		$testrun = $this->testruns->find($testRunId);
		$testplan = $testrun->testplan()->firstOrFail();
		$executionStatuses = $this->executionStatuses->all();

        // TODO's:
		// get test suites
		// create right array

		$testsuites = $this->testruns->getTestSuites($testRunId)->get();
		$testcases = $this->testruns->getTestCases($testRunId);

		$ts = array();
		foreach ($testsuites as $testsuite)
		{
			$tcs = array();
			foreach ($testcases as $testcase)
			{
				if ($testcase->test_suite_id == $testsuite->id)
				{
					$testcaseObj = $testcase;
					$tcs[$testcase->id] = $testcaseObj;
				}
			}
			$testsuiteObj = (object) $testsuite->toArray(); // detach
			$testsuiteObj->testcases = $tcs;
			$ts[$testsuite->id] = $testsuiteObj;
		}

		$producer = new \Nestor\Util\JUnitProducer();

		$document = $producer->produce($ts);
		// Create doc and put in args

		$download = Input::get('download');
		if (isset($download) && $download == 'true')
		{
			return Response::make($document->saveXML(), '200', array(
			    'Content-Type' => 'application/octet-stream',
			    'Content-Disposition' => 'attachment; filename="junit.xml"'
			));
		}

		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Runs for Test Plan %s', $testplan->name), URL::to(sprintf('/execution/testruns?test_plan_id=%d', $testplan->id)))->
			add(sprintf('Test Run %s', $testrun->name), URL::to('/execution/testruns/' . $testRunId));

		$args = array();
		$args['testrun'] = $testrun;
		$args['testplan'] = $testplan;
		$args['document'] = $document->saveXML();
		$args['current_project'] = $this->currentProject;
		$args['execution_statuses'] = $executionStatuses;

		return $this->theme->scope('execution.testrun.junit', $args)->render();
	}

}