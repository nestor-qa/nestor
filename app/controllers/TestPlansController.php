<?php

use \Theme;
use \Input;
use \DB;
use Nestor\Repositories\TestPlanRepository;
use Nestor\Repositories\TestCaseRepository;
use Nestor\Repositories\NavigationTreeRepository;

class TestPlansController extends \NavigationTreeController {

	/**
	 * The test plan repository implementation.
	 *
	 * @var Nestor\Repositories\TestPlanRepository
	 */
	protected $testplans;

	/**
	 * The test case repository implementation.
	 *
	 * @var Nestor\Repositories\TestCaseRepository
	 */
	protected $testcases;

	/**
	 * @var Nestor\Repositories\NavigationTreeRepository
	 */
	protected $nodes;

	protected $theme;

	public $restful = true;

	public function __construct(TestPlanRepository $testplans, TestCaseRepository $testcases, NavigationTreeRepository $nodes)
	{
		parent::__construct();
		$this->testplans = $testplans;
		$this->testcases = $testcases;
		$this->nodes = $nodes;
		$this->theme->setActive('planning');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Planning');
		$args = array();
		$project = $this->getCurrentProject();
		$projectId = $project->id;
		$args['testplans'] = $this->testplans->findByProjectId($projectId);
		return $this->theme->scope('testplan.index', $args)->render();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Planning', URL::to('/planning'))->
			add('Create new test plan');
		$args = array();
		$args['project'] = $this->getCurrentProject();
		return $this->theme->scope('testplan.create', $args)->render();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		Log::info('Creating test plan...');

		$testplan = $this->testplans->create(
				Input::get('project_id'),
				Input::get('name'),
				Input::get('description')
		);

		if ($testplan->isValid() && $testplan->isSaved())
		{
			return Redirect::to(sprintf('/planning/%s', $testplan->id))
				->with('success', 'Test plan created. Add some tests to it now.');
		} else {
			return Redirect::to('/planning/create')
				->withInput()
				->withErrors($testplan->errors());
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
		$testplan = $this->testplans->find($id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Planning', URL::to('/planning'))->
			add(sprintf('Test plan %s', $testplan->name));
		$args = array();
		$args['testplan'] = $testplan;
		$args['testcases'] = $testplan->testcases;
		return $this->theme->scope('testplan.show', $args)->render();
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$testplan = $this->testplans->find($id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Planning', URL::to('/planning'))->
			add(sprintf('Test plan %s', $testplan->name));
		$args = array();
		$args['testplan'] = $testplan;
		$args['project'] = $this->getCurrentProject();
		return $this->theme->scope('testplan.edit', $args)->render();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		Log::info('Updating test plan...');

		$testplan = $this->testplans->update(
				$id,
				Input::get('project_id'),
				Input::get('name'),
				Input::get('description')
		);

		if ($testplan->isValid() && $testplan->isSaved())
		{
			return Redirect::route('testplans.show', $id)
				->with('success', 'The test plan was updated');
		} else {
			return Redirect::route('testplans.edit', $id)
				->withInput()
				->withErrors($testplan->errors());
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
		Log::info('Destroying test plan...');
		$testplan = $this->testplans->find($id);
		$this->testplans->delete($id);

		return Redirect::route('testplans.index')
			->with('success', sprintf('The test plan %s has been deleted', $testplan->name));
	}

	/**
	 * Displays navigation tree for managing test cases of a test plan.
	 */
	public function addTestCases($id)
	{
		// This is the current test plan
		$testplan = $this->testplans->find($id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Planning', URL::to('/planning'))->
			add(sprintf('Test plan %s', $testplan->name), URL::to(sprintf('/planning/%s', $testplan->id)))->
			add('Add Test Cases');
		$currentProject = $this->getCurrentProject();
		$nodesSelected = array();
		$testcases = $testplan->testcasesDetached();

		foreach ($testcases as $testcase)
		{
			$nodesSelected[$testcase->id] = TRUE;
		}

		$nodes = $this->nodes->children('1-'.$currentProject->id, 1 /* length*/);
		$navigationTree = $this->createNavigationTree($nodes, '1-'.$currentProject->id);
		$navigationTreeHtml = $this->createTestPlanTreeHTML($navigationTree, "2-" . $testplan->id, $this->theme->getThemeName(), $nodesSelected);
		
		$args = array();
		$args['testplan'] = $testplan;
		$args['nodesSelected'] = $nodesSelected;
		$args['navigation_tree'] = $navigationTree;
		$args['navigation_tree_html'] = $navigationTreeHtml;
		$args['current_project'] = $this->currentProject;
		return $this->theme->scope('testplan.addTestCases', $args)->render();
	}

	public function storeTestCases($id)
	{
		$testplan = $this->testplans->find($id);
		$existingTestcases = $testplan->testcases()->get();
		$length = count($_POST);
		$nodesSelected = array();
		$testcases = array();
		foreach ($_POST as $entry => $value)
		{
			if (strpos($entry, 'ft_') === 0 && strpos($entry, 'ft_1_active') !== 0)
			{
				if (is_array($value))
				{
					foreach ($value as $tempValue)
						$nodesSelected[] = $tempValue;
				}
				else 
				{
					$nodesSelected[] = $value;
				}
			}
		}
		foreach ($nodesSelected as $node)
		{
			$children = $this->nodes->children($node);
			$this->getTestCasesFrom($children, $testcases);
		}
		// What to remove?
		$testcasesForRemoval = array();
		foreach ($existingTestcases as $existing)
		{
			$found = FALSE;
			foreach ($testcases as $testcase)
			{
				if ($existing->id == $testcase->id) 
				{
					$found = TRUE;
				}
			}
			if (!$found)
			{
				$testcasesForRemoval[] = $existing;
			}
		}
		// What do add?
		$testcasesForAdding = array();
		foreach ($testcases as $testcase)
		{
			$found = FALSE;
			foreach ($existingTestcases as $existing)
			{
				if ($testcase->id == $existing->id) 
				{
					$found = TRUE;
				}
			}
			if (!$found)
			{
				$testcasesForAdding[] = $testcase;
			}
		}

		foreach ($testcasesForAdding as $addMe)
		{
			Log::info(sprintf('Adding testcase %s to test plan %s', $addMe->name, $testplan->name));
			$testplan->testcases()->attach($addMe);
		}

		foreach ($testcasesForRemoval as $removeMe)
		{
			Log::info(sprintf('Removing testcase %s from test plan %s', $removeMe->name, $testplan->name));
			$testplan->testcases()->detach($removeMe);
		}

		return Redirect::to('/planning/' . $id)
				->with('success', sprintf('%d test cases added, and %d removed', count($testcasesForAdding), count($testcasesForRemoval)));
	}

	protected function getTestCasesFrom($children, &$testcases)
	{

		foreach ($children as $child)
		{
			$executionType = $child->getDescendantExecutionType();
			if ($executionType == 3)
			{
				$nodeId = $child->getDescendantNodeId();
				$testcases[$nodeId] = $this->testcases->find($nodeId);
			}
			if (isset($child->children) && !empty($child->children))
			{
				$this->getTestCasesFrom($children, $testcases);
			}
		}
	}

}