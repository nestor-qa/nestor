<?php

use \Theme;
use \Input;
use \DB;
use Nestor\Repositories\TestPlanRepository;
use Nestor\Repositories\TestCaseRepository;
use Nestor\Repositories\TestRunRepository;
use Nestor\Repositories\NavigationTreeRepository;
use Nestor\Repositories\ExecutionStatusRepository;
use Nestor\Repositories\ExecutionRepository;
use Nestor\Repositories\StepExecutionRepository;

class TestRunsController extends \NavigationTreeController {

	/**
	 * The test plan repository implementation.
	 *
	 * @var Nestor\Repositories\TestPlanRepository
	 */
	protected $testplans;

	/**
	 * The test run repository implementation.
	 *
	 * @var Nestor\Repositories\TestRunRepository
	 */
	protected $testruns;

	/**
	 * The test case repository implementation.
	 *
	 * @var Nestor\Repositories\TestCaseRepository
	 */
	protected $testcases;

	/**
	 * The execution status repository implementation.
	 *
	 * @var Nestor\Repositories\ExecutionStatusRepository
	 */
	protected $executionStatuses;

	/**
	 * The execution repository implementation.
	 *
	 * @var Nestor\Repositories\ExecutionRepository
	 */
	protected $executions;

	/**
	 * The step execution repository implementation.
	 *
	 * @var Nestor\Repositories\StepExecutionRepository
	 */
	protected $stepExecutions;

	/**
	 * @var Nestor\Repositories\NavigationTreeRepository
	 */
	protected $nodes;

	protected $theme;

	public $restful = true;

	public function __construct(TestPlanRepository $testplans, 
		TestCaseRepository $testcases, 
		TestRunRepository $testruns, 
		NavigationTreeRepository $nodes, 
		ExecutionStatusRepository $executionStatuses,
		ExecutionRepository $executions,
		StepExecutionRepository $stepExecutions)
	{
		parent::__construct();
		$this->testplans = $testplans;
		$this->testcases = $testcases;
		$this->testruns = $testruns;
		$this->nodes = $nodes;
		$this->executionStatuses = $executionStatuses;
		$this->executions = $executions;
		$this->stepExecutions = $stepExecutions;
		$this->theme->setActive('execution');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$test_plan_id = Input::get('test_plan_id');
		$testplan = $this->testplans->find($test_plan_id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Runs for Test Plan %s', $testplan->name));
		$testruns = $this->testruns->findByTestPlanId($test_plan_id);
		$args = array();
		$args['testruns'] = $testruns;
		$args['testplan'] = $testplan;
		return $this->theme->scope('execution.testrun.index', $args)->render();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$test_plan_id = Input::get('test_plan_id');
		$testplan = $this->testplans->find($test_plan_id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Create Test Run for Test Plan %s', $testplan->name));
		$args = array();
		$args['testplan'] = $testplan;
		return $this->theme->scope('execution.create', $args)->render();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		Log::info('Creating test run...');

		$testrun = $this->testruns->create(
				Input::get('test_plan_id'),
				Input::get('name'),
				Input::get('description')
		);

		if ($testrun->isValid() && $testrun->isSaved())
		{
			return Redirect::to('/executions/')
				->with('success', 'A new test run has been created');
		} else {
			return Redirect::to('/executions/create?test_plan_id=' . Input::get('test_plan_id'))
				->withInput()
				->withErrors($testrun->errors());
		}
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
		$testrun = $this->testruns->find($id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Run %s', $testrun->name));
		$args['testrun'] = $testrun;
		$args['testplan'] = $testrun->testplan;
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
		$testrun = $this->testruns->find($id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Run %s', $testrun->name));
		$args['testrun'] = $testrun;
		$args['testplan'] = $testrun->testplan;
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
		Log::info('Updating test run...');

		$testrun = $this->testruns->update(
				$id,
				Input::get('test_plan_id'),
				Input::get('name'),
				Input::get('description')
		);

		if ($testrun->isValid() && $testrun->isSaved())
		{
			return Redirect::route('execution.testruns.show', $id)
				->with('success', 'The test run was updated');
		} else {
			return Redirect::route('execution.testruns.edit', $id)
				->withInput()
				->withErrors($testrun->errors());
		}
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

	public function runGet($test_run_id) 
	{
		Log::info(sprintf('Executing Test Run %d', $test_run_id));
		$currentProject = $this->getCurrentProject();
		$testrun = $this->testruns->find($test_run_id);
		$testplan = $testrun->testplan;
		$testcases = $testplan->testcases();

		Log::debug('Creating breadcrumb');
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Runs for Test Plan %s', $testplan->name), URL::to(sprintf('/execution/testruns?test_plan_id=%d', $testplan->id)))->
			add(sprintf('Test Run %s', $testrun->name));

		$showOnly = array(); // Our filter

		foreach ($testcases as $testcase)
		{
			$showOnly[$testcase->id] = TRUE;
		}

		$nodes = $this->nodes->children('1-'.$currentProject->id, 1 /* length*/);
		$navigationTree = $this->createNavigationTree($nodes, '1-'.$currentProject->id);
		$navigationTreeHtml = $this->createTestRunTreeHTML($navigationTree, $testrun->id, $showOnly);
		
		$args = array();
		$args['testrun'] = $testrun;
		$args['testplan'] = $testplan;
		$args['testcases'] = $testcases;
		$args['navigation_tree'] = $navigationTree;
		$args['navigation_tree_html'] = $navigationTreeHtml;
		$args['current_project'] = $this->currentProject;

		return $this->theme->scope('execution.testrun.run', $args)->render();
	}

	public function runTestCase($testRunId, $testCaseId) 
	{
		Log::info(sprintf('Executing Test Run %d, Test Case %d', $testRunId, $testCaseId));
		$currentProject = $this->getCurrentProject();
		$testrun = $this->testruns->find($testRunId);
		$testplan = $testrun->testplan;
		$testcases = $testplan->testcases();
		$testcase = $this->testcases->find($testCaseId);
		$testcaseVersion = $testcase->latestVersion();
		$executionStatuses = $this->executionStatuses->all();

		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Execution', URL::to('/execution'))->
			add(sprintf('Test Runs for Test Plan %s', $testplan->name), URL::to(sprintf('/execution/testruns?test_plan_id=%d', $testplan->id)))->
			add(sprintf('Test Run %s', $testrun->name));

		$showOnly = array(); // Our filter

		foreach ($testcases as $tempTestcase)
		{
			$showOnly[$tempTestcase->id] = TRUE;
		}

		$nodes = $this->nodes->children('1-'.$currentProject->id, 1 /* length*/);
		$navigationTree = $this->createNavigationTree($nodes, '1-'.$currentProject->id);
		$navigationTreeHtml = $this->createTestRunTreeHTML($navigationTree, $testrun->id, $showOnly, $testCaseId);
		
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
		$args['testrun'] = $testrun;
		$args['testplan'] = $testplan;
		$args['testcases'] = $testcases;
		$args['testcase'] = $testcase;
		$args['testcaseVersion'] = $testcaseVersion;
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

}