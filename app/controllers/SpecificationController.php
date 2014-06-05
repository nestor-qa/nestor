<?php

use \Input;
use \HTML;
use Nestor\Repositories\ProjectRepository;
use Nestor\Repositories\TestCaseRepository;
use Nestor\Repositories\ExecutionTypeRepository;
use Nestor\Repositories\NavigationTreeRepository;

use Nestor\Repositories\ExecutionStatusRepository;

class SpecificationController extends \NavigationTreeController {

	/**
	 *
	 * @var Nestor\Repositories\ProjectRepository;
	 */
	protected $projects;

	/**
	 *
	 * @var Nestor\Repositories\TestCaseRepository;
	 */
	protected $testcases;

	/**
	 * The execution type repository implementation.
	 *
	 * @var Nestor\Repositories\ExecutionTypeRepository
	 */
	protected $executionTypes;

	/**
	 * @var Nestor\Repositories\NavigationTreeRepository
	 */
	protected $nodes;

	/**
	 * The execution status repository implementation.
	 *
	 * @var Nestor\Repositories\ExecutionStatusRepository
	 */
	protected $executionStatuses;

	/**
	 * Current project in Session.
	 * @var Project
	 */
	protected $currentProject;

	/**
	 * Constructor.
	 *
	 * @param Nestor\Repositories\ProjectRepository         $projects
	 * @param Nestor\Repositories\ExecutionTypeRepository   $executionTypes
	 * @param Nestor\Repositories\NavigationTreeRepository  $nodes
	 * @return SpecificationController
	 */
	public function __construct(
		ProjectRepository $projects, 
		TestCaseRepository $testcases, 
		ExecutionTypeRepository $executionTypes, 
		NavigationTreeRepository $nodes, 
		ExecutionStatusRepository $executionStatuses)
	{
		parent::__construct();
		$this->projects = $projects;
		$this->testcases = $testcases;
		$this->executionTypes = $executionTypes;
		$this->nodes = $nodes;
		$this->executionStatuses = $executionStatuses;
		$this->theme->setActive('specification');
	}

	/**
	 * Specification index controller.
	 *
	 * /specification/nodes
	 */
	public function getIndex()
	{
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Specification');
		$currentProject = $this->getCurrentProject();
		$nodes = $this->nodes->children('1-'.$currentProject->id, 1 /* length*/);
//  		$queries = DB::getQueryLog();
//  		$last_query = end($queries);
		$navigationTree = $this->createNavigationTree($nodes, '1-'.$currentProject->id);
		$navigationTreeHtml = $this->createTreeHTML($navigationTree, "", $this->theme->getThemeName());
		$args = array();
		$args['navigation_tree'] = $navigationTree;
		$args['navigation_tree_html'] = $navigationTreeHtml;
		$args['current_project'] = $this->currentProject;
		return $this->theme->scope('specification.index', $args)->render();
	}

	/**
	 * Specification nodes controller.
	 *
	 * /specification/nodes/(:any)
	 */
	public function getNodes($nodeId)
	{
		$currentProject = $this->getCurrentProject();
		$nodes = $this->nodes->children('1-'.$currentProject->id, 1 /* length*/);
		$navigationTree = $this->createNavigationTree($nodes, '1-'.$currentProject->id);
		$navigationTreeHtml = $this->createTreeHTML($navigationTree, $nodeId, $this->theme->getThemeName());
		$args = array();

		try
		{
			$node = $this->nodes->find($nodeId, $nodeId);
		}
		catch (Exception $mnfe)
		{
			return Redirect::to('/specification/')
				->with('flash', sprintf('The node %s does not belong to the current selected project', $nodeId));
		}
		if (!$this->isNodeInTree($navigationTree, $node))
		{
			return Redirect::to('/specification/')
				->with('flash', sprintf('The node %s does not belong to the current selected project', $nodeId));
		}
		$args['node'] = $node;
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Specification', URL::to('/specification/'))->
			add(sprintf('Node %s-%s', $node->node_type_id, $node->node_id));

		// Create specific parameters depending on execution type
		if (isset($node) && $node->node_type_id == 2) // Test Suite?
		{
			$execution_types = $this->executionTypes->all();
			$args['execution_types'] = $execution_types;
			$execution_types_ids = array();
			foreach ($args['execution_types'] as $execution_type)
			{
				$execution_types_ids[$execution_type->id] = $execution_type->name;
			}
			$args['execution_type_ids'] = $execution_types_ids;
			$execution_statuses = $this->executionStatuses->all();
			$args['execution_statuses'] = $execution_statuses;
			$execution_statuses_ids = array();
			foreach ($args['execution_statuses'] as $execution_status) 
			{
				if ($execution_status->id == 1 || $execution_status->id == 2)
					continue; // Skip NOT RUN
				$execution_statuses_ids[$execution_status->id] = $execution_status->name;
			}
			$args['execution_statuses_ids'] = $execution_statuses_ids;
		}
		else if (isset($node) && $node->node_type_id == 3) // Test Case?
		{
			$execution_types = $this->executionTypes->all();
			$testcase = $this->testcases->find($node->node_id);
			if (isset($testcase) && !is_null($testcase))
			{
				foreach ($execution_types as $execution_type)
				{
					if ($execution_type->id == $testcase->execution_type_id)
					{
						$testcase->execution_type_name = $execution_type->name;
					}
				}
			}
			$args['testcase'] = $testcase;
		}
		$args['navigation_tree_html'] = $navigationTreeHtml;
		$args['navigation_tree'] = $navigationTree;
		$args['current_project'] = $this->currentProject;
		return $this->theme->scope('specification.index', $args)->render();
	}

	public function postMoveNode() 
	{
		$descendant = Input::get('descendant');
		$ancestor = Input::get('ancestor');
		Log::debug(sprintf('Moving %s under %s', $descendant, $ancestor));
		$this->nodes->move($descendant, $ancestor);
		return Response::json('OK');
	}

}