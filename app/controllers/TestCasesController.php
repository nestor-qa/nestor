<?php

use \Theme;
use \Input;
use \DB;
use \Validator;
use Nestor\Repositories\TestCaseRepository;
use Nestor\Repositories\ExecutionTypeRepository;
use Nestor\Repositories\NavigationTreeRepository;
use Nestor\Repositories\TestCaseStepRepository;
use Nestor\Repositories\ExecutionStatusRepository;

class TestCasesController extends \BaseController {

	/**
	 * The test case repository implementation.
	 *
	 * @var Nestor\Repositories\TestCaseRepository
	 */
	protected $testcases;

	/**
	 * The execution type repository implementation.
	 *
	 * @var Nestor\Repositories\ExecutionTypeRepository
	 */
	protected $executionTypes;

	/**
	 * The navigation tree node repository implementation.
	 *
	 * @var Nestor\Repositories\NavigationTreeRepository
	 */
	protected $nodes;

	/**
	 * The test case repository implementation.
	 *
	 * @var Nestor\Repositories\TestCaseStepRepository
	 */
	protected $testcaseSteps;

	/**
	 * The execution status repository implementation.
	 *
	 * @var Nestor\Repositories\ExecutionStatusRepository
	 */
	protected $executionStatuses;

	protected $theme;

	public $restful = true;

	public function __construct(
		TestCaseRepository $testcases, 
		ExecutionTypeRepository $executionTypes, 
		NavigationTreeRepository $nodes, 
		TestCaseStepRepository $testcaseSteps, 
		ExecutionStatusRepository $executionStatuses)
	{
		parent::__construct();
		$this->testcases = $testcases;
		$this->executionTypes = $executionTypes;
		$this->nodes = $nodes;
		$this->testcaseSteps = $testcaseSteps;
		$this->executionStatuses = $executionStatuses;
		$this->theme->setActive('testcases');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return Redirect::to('/specification/');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return Redirect::to('/specification/');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$testcase = null;
		$testcaseVersion = null;
		$navigationTreeNode = null;
		Log::info('Creating test case...');
		$pdo = null;
		try 
		{
			if (!$this->testcases->isNameAvailable(0, Input::get('test_suite_id'), Input::get('name')))
			{
				throw new Exception('Test case not created: Name already taken.');
			}

			$pdo = DB::connection()->getPdo();
    		$pdo->beginTransaction();
			list($testcase, $testcaseVersion) = $this->testcases->create(
					Input::get('project_id'),
					Input::get('test_suite_id'),
					Input::get('execution_type_id'),
					Input::get('name'),
					Input::get('description'),
					Input::get('prerequisite')
			);
			
			$stepOrders = Input::get('step_order');
			$stepDescriptions = Input::get('step_description');
			$stepExpectedResults = Input::get('step_expected_result');
			$stepExecutionStatuses = Input::get('step_execution_status');
			if (isset($stepOrders) && is_array($stepOrders)) 
			{
				for($i = 0; $i < count($stepOrders); ++$i)
				{
					$stepOrder = $stepOrders[$i];
					$stepDescription = $stepDescriptions[$i];
					$stepExpectedResult = $stepExpectedResults[$i];
					$stepExecutionStatus = $stepExecutionStatuses[$i];

					$testcaseStep = $this->testcaseSteps->create($testcaseVersion->id, $stepOrder, $stepDescription, $stepExpectedResult, $stepExecutionStatus);
					if (!$testcaseStep->isValid() || !$testcaseStep->isSaved())
					{
						Log::warning('Failed to save a test step. Rolling back.');
						throw new Exception('Failed to persist a test case. Check your input parameters.');
					}
				}
				Log::debug('Test steps created');
			}
			else
			{
				Log::debug('No test steps created');
			}

			$ancestor = Input::get('ancestor');
			if ($testcase->isValid() && $testcase->isSaved() && $testcaseVersion->isValid() && $testcaseVersion->isSaved())
			{
				Log::debug('Test Case valid and saved');
				$navigationTreeNode = $this->nodes->create(
						$ancestor,
						'3-' . $testcase->id,
						$testcase->id,
						3,
						$testcaseVersion->name
				);
				if ($navigationTreeNode)
				{
					Log::debug('Committing transaction');
					$pdo->commit();
				} 
				else
				{
					Log::debug('Failed to create navigation node. Rolling back transaction');
					$pdo->rollBack();
				}
			}
			else
			{
				Log::debug('Failed to create test case. Rolling back transaction');
				$pdo->rollBack();
			}
		} catch (\PDOException $e) {
			if (!is_null($pdo))
				$pdo->rollBack();
			Log::warning('Failed to store new Test Case. PDO error: ' . $e->getMessage());
			$messages = new Illuminate\Support\MessageBag;
			$messages->add('nestor.customError', $e->getMessage());
			return Redirect::to('/specification/')->withInput()->withErrors($messages);
		} catch (\Exception $e) {
			if (!is_null($pdo))
				$pdo->rollBack();
			Log::warning('Failed to store new Test Case. Error: ' . $e->getMessage());
			$messages = new Illuminate\Support\MessageBag;
			$messages->add('nestor.customError', $e->getMessage());
			return Redirect::to('/specification/')->withInput()->withErrors($messages);
		}
		if ($testcase->isSaved() && $testcaseVersion->isSaved() && $navigationTreeNode)
		{
			return Redirect::to('/specification/nodes/' . '3-' . $testcase->id)
				->with('success', 'A new test case has been created');
		} else {
			if (!$testcase->isSaved())
			{
				Log::warning('Failed to store new Test Case: ' . $testcase->errors());
				$messages = new Illuminate\Support\MessageBag;
				$messages->add('nestor.customError', 'Failed to store new Test Case: ' . $testcase->errors());
			}
			else if (!$testcaseVersion->isSaved())
			{
				Log::warning('Failed to store new Test Case Version: ' . $testcaseVersion->errors());
				$messages = new Illuminate\Support\MessageBag;
				$messages->add('nestor.customError', 'Failed to store new Test Case Version: ' . $testcaseVersion->errors());
			}
			
			return Redirect::to('/specification/')
				->withInput()
				->withErrors($messages);
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
		return Redirect::to('/specification/nodes/3-' . $id);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$testcase = $this->testcases->find($id);
		$args = array();
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Specification', URL::to('/specification/'))->
			add(sprintf('Edit test case %s', $testcase->name));
		$args['testcase'] = $testcase;
		$args['execution_types'] = $this->executionTypes->all();
		$executionStatusesCol = $this->executionStatuses->all();
		$execution_statuses = array();
		foreach ($executionStatusesCol as $execution_status)
		{
			if ($execution_status->id != 1 && $execution_status->id != 2) 
			{
				$o = new stdClass();
				$o->name = $execution_status->name;
				$o->id = $execution_status->id;
				$execution_statuses[] = $o;
			}
		}
		$args['execution_statuses'] = json_encode($execution_statuses);
		$execution_statuses_ids = array();
		foreach ($executionStatusesCol as $execution_status) 
		{
			if ($execution_status->id == 1 || $execution_status->id == 2)
				continue; // Skip NOT RUN
			$execution_statuses_ids[$execution_status->id] = $execution_status->name;
		}
		$args['execution_statuses_ids'] = $execution_statuses_ids;
		$execution_types_ids = array();
		foreach ($args['execution_types'] as $execution_type)
		{
			$execution_types_ids[$execution_type->id] = $execution_type->name;
		}
		$args['execution_type_ids'] = $execution_types_ids;
		return $this->theme->scope('testcase.edit', $args)->render();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$testcase = null;
		$testcaseVersion = null;
		$navigationTreeNode = null;
		Log::info('Updating test case...');
		$pdo = null;
		try {
			$pdo = DB::connection()->getPdo();
			$pdo->beginTransaction();

			if (!$this->testcases->isNameAvailable($id, Input::get('test_suite_id'), Input::get('name')))
			{
				throw new Exception('Test case not updated: Name already taken.');
			}

			list($testcase, $testcaseVersion) = $this->testcases->update($id,
				Input::get('project_id'),
				Input::get('test_suite_id'),
				Input::get('execution_type_id'),
				Input::get('name'),
				Input::get('description'),
				Input::get('prerequisite'));

			if (!$testcaseVersion->isSaved()) 
			{
				throw new Exception('Test case version not updated: ' . $testcaseVersion->errors());
			}

			Log::info('Updating test case steps...');
			$existingSteps = $testcaseVersion->steps->all();

			// update test case steps
			$stepIds = Input::get('step_id');
			$stepOrders = Input::get('step_order');
			$stepDescriptions = Input::get('step_description');
			$stepExpectedResults = Input::get('step_expected_result');
			$stepExecutionStatuses = Input::get('step_execution_status');
			if (isset($stepOrders) && is_array($stepOrders)) 
			{
				for($i = 0; $i < count($stepOrders); ++$i)
				{
					$stepId = $stepIds[$i];
					$stepOrder = $stepOrders[$i];
					$stepDescription = $stepDescriptions[$i];
					$stepExpectedResult = $stepExpectedResults[$i];
					$stepExecutionStatus = $stepExecutionStatuses[$i];

					if (strcmp($stepId, "-1") !== 0)
					{
						$testcaseStep = $this->testcaseSteps->update($stepId, $testcaseVersion->id, $stepOrder, $stepDescription, $stepExpectedResult, $stepExecutionStatus);
					}
					else
					{
						$testcaseStep = $this->testcaseSteps->create($testcaseVersion->id, $stepOrder, $stepDescription, $stepExpectedResult, $stepExecutionStatus);
					}
					if (!$testcaseStep->isValid() || !$testcaseStep->isSaved())
					{
						Log::warning('Failed to save a test step. Rolling back.');
						throw new Exception('Failed to persist a test case step. Check your input parameters.');
					}
				}
			}

			if (empty($stepIds))
			{
				foreach ($existingSteps as $existingStep)
				{
					Log::info("Deleting test case step: " . $existingStep->id);
					$this->testcaseSteps->delete($existingStep->id);
				}
			} 
			else 
			{
				top: foreach ($existingSteps as $existingStep) 
				{
					$remove = true;
					foreach ($stepIds as $stepId)
					{
						if ($stepId == $existingStep->id)
						{
							$remove = false;
							continue 2;
						}
					}
					Log::info("Deleting test case step: " . $existingStep->id);
					$this->testcaseSteps->delete($existingStep->id);
				}
			}

			$navigationTreeNode = $this->nodes->updateDisplayNameByDescendant(
				'3-'.$testcase->id,
				$testcaseVersion->name);
			$pdo->commit();	
		} catch (\Exception $e) {
			Log::error($e);
			if (!is_null($pdo))
				$pdo->rollBack();
			return Redirect::to(sprintf('/testcases/%d/edit', $id))
				->withInput()
				->with('error', $e->getMessage());
		}
		if (!is_null($testcase) && $testcase->isSaved() && $testCase)
		{
			Log::info(sprintf('Test case %d updated.', $testcase->id));
			return Redirect::to(sprintf('/specification/nodes/%s-%s', 3, $testcase->id))
				->with('success', 'Test case updated');
		} else {
			return Redirect::to(sprintf('/testcases/%d/edit', $id))
				->withInput()
				->withErrors($testcase->errors());
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
		Log::info(sprintf('Deleting test case %d...', $id));
		$parent = null;
		$pdo = null;
		try {
			$pdo = DB::connection()->getPdo();
			$pdo->beginTransaction();
			$parent = $this->nodes->parent('3-'.$id);
			Log::info(sprintf('The parent node of the deleted is %s', $parent->ancestor));
			$testcasesDeleted = $this->testcases->delete($id);
			$nodesDeleted = $this->nodes->delete('3-'.$id);

			if ($testcasesDeleted !== 1)
			{
				throw new Exception('Failed to delete test case');
			}

			// if ($nodesDeleted !== 1)
			// {
			// 	$queries = DB::getQueryLog();
			// 	$last_query = end($queries);
			// 	Log::info($last_query);
			// 	throw new Exception('Failed to delete node');
			// }

			$pdo->commit();

			return Redirect::to('/specification/nodes/' . $parent->ancestor);
		} catch (\Exception $e) {
			Log::error($e);
			if (!is_null($pdo))
				$pdo->rollBack();
			return Redirect::to('/testcases/' . $id)->withInput();
		}
	}

}