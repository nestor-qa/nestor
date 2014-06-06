<?php

use \Theme;
use \Input;
use \DB;
use Nestor\Repositories\TestSuiteRepository;
use Nestor\Repositories\NavigationTreeRepository;

class TestSuitesController extends \BaseController {

	/**
	 * The test suite repository implementation.
	 *
	 * @var Nestor\Repositories\TestSuiteRepository
	 */
	protected $testsuites;

	/**
	 * The navigation tree node repository implementation.
	 *
	 * @var Nestor\Repositories\NavigationTreeRepository
	 */
	protected $nodes;

	protected $theme;

	public $restful = true;

	public function __construct(TestSuiteRepository $testsuites, NavigationTreeRepository $nodes)
	{
		parent::__construct();
		$this->testsuites = $testsuites;
		$this->nodes = $nodes;
		$this->theme->setActive('testsuites');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return Redirect::to('/specification');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return Redirect::to('/specification');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$testsuite = null;
		$navigationTreeNode = null;
		Log::info('Creating test suite...');
		$pdo = null;
		try {
			$pdo = DB::connection()->getPdo();
    		$pdo->beginTransaction();
			$testsuite = $this->testsuites->create(
					Input::get('project_id'),
					Input::get('name'),
					Input::get('description')
			);
			$ancestor = Input::get('ancestor');
			if ($testsuite->isValid() && $testsuite->isSaved())
			{
				$navigationTreeNode = $this->nodes->create(
						$ancestor,
						'2-' . $pdo->lastInsertId(),
						$pdo->lastInsertId(),
						2,
						$testsuite->name
				);
				if ($navigationTreeNode)
				{
					$pdo->commit();
				}
			}
		} catch (\PDOException $e) {
			if (!is_null($pdo))
				$pdo->rollBack();
			return Redirect::to(URL::previous())
	 			->withInput();
		}
		if ($testsuite->isSaved() && $navigationTreeNode)
		{
			return Redirect::to('/specification/nodes/' . '2-' . $testsuite->id)
				->with('success', 'A new test suite has been created');
		} else {
			return Redirect::to(URL::previous())
				->withInput()
				->withErrors($testsuite->errors());
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
		$testsuite = $this->testsuites->find($id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Specification', URL::to('/specification'))->
			add(sprintf('Test Suite %s', $testsuite->name));
		$args = array();
		$args['testsuite'] = $testsuite;
		return $this->theme->scope('testsuite.show', $args)->render();
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$testsuite = $this->testsuites->find($id);
		$this->theme->breadcrumb()->
			add('Home', URL::to('/'))->
			add('Specification', URL::to('/specification'))->
			add(sprintf('Test Suite %s', $testsuite->name));
		$args = array();
		$args['testsuite'] = $testsuite;
		return $this->theme->scope('testsuite.edit', $args)->render();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$testsuite = null;
		$navigationTreeNode = null;
		Log::info('Updating test suite...');
		$pdo = null;
		try {
			$pdo = DB::connection()->getPdo();
			$pdo->beginTransaction();
			$testsuite = $this->testsuites->update(
							$id,
							Input::get('project_id'),
							Input::get('name'),
							Input::get('description'));
			if ($testsuite->isValid() && $testsuite->isSaved())
			{
				$navigationTreeNode = $this->nodes->updateDisplayNameByDescendant(
						'2-'.$testsuite->id,
						$testsuite->name);
				$pdo->commit();
			}
		} catch (\PDOException $e) {
			if (!is_null($pdo))
				$pdo->rollBack();
			return Redirect::to('/specification/')->withInput();
		}

		if ($testsuite->isSaved())
		{
			return Redirect::route('testsuites.show', $id)->with('success', 'The test suite was updated');
		} else {
			return Redirect::route('testsuites.edit', $id)
				->withInput()
				->withErrors($testsuite->errors());
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
		$testsuite = null;
		$navigationTreeNode = null;
		Log::info('Destroying test suite...');
		$pdo = null;
		try {
			$pdo = DB::connection()->getPdo();
			$pdo->beginTransaction();
			$testsuite = $this->testsuites->find($id);
			$this->testsuites->delete($id);
			$navigationTreeNode = $this->nodes->find('2-' . $testsuite->id, '2-' . $testsuite->id);
			$this->nodes->deleteWithAllChildren($navigationTreeNode->ancestor, $navigationTreeNode->descendant);
			$pdo->commit();
		} catch (\PDOException $e) {
			if (!is_null($pdo))
				$pdo->rollBack();
			return Redirect::to('/specification/')->withInput();
		}

		return Redirect::to('/specification')
			->with('flash', sprintf('The test suite %s has been deleted', $testsuite->name));
	}

}