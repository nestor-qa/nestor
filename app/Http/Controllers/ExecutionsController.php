<?php

namespace Nestor\Http\Controllers;

use Log;
use Parsedown;
use Validator;

use Illuminate\Http\Request;

use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;

use Nestor\Http\Requests;
use Nestor\Repositories\ExecutionsRepository;
use Nestor\Repositories\TestCasesRepository;
use Nestor\Validators\ExecutionsValidator;

class ExecutionsController extends Controller
{

    /**
     * @var ExecutionsRepository
     */
    protected $executionsRepository;

    /**
     *
     * @var TestCasesRepository $testCasesRepository
     */
    protected $testCasesRepository;

    public function __construct(ExecutionsRepository $executionsRepository, TestCasesRepository $testCasesRepository)
    {
        $this->executionsRepository = $executionsRepository;
        $this->testCasesRepository = $testCasesRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::debug("Returning paginated executions");
        $testPlanId = $request->input('project_id', -1);
        return $this->executionsRepository->scopeQuery(function ($query) use ($testPlanId) {
            return $query->where('test_plan_id', $testPlanId)->orderBy('name', 'ASC');
        })->paginate();
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    public function showTestCase($testPlanId, $testRunId, $testSuiteId, $id)
    {
        $testCase = $this->testCasesRepository->findTestCaseWithVersion($id);
        $testCase['formatted_description'] = Parsedown::instance()->text($testCase['version']['description']);
        $testCase['formatted_prerequisite'] = Parsedown::instance()->text($testCase['version']['prerequisite']);
        return $testCase;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  string            $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
