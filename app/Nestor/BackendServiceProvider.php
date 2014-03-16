<?php namespace Nestor;

use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('Nestor\Repositories\UserRepository', 'Nestor\Repositories\DbUserRepository');
		$this->app->singleton('Nestor\Repositories\ProjectRepository', 'Nestor\Repositories\DbProjectRepository');
		$this->app->singleton('Nestor\Repositories\ProjectStatusRepository', 'Nestor\Repositories\DbProjectStatusRepository');
		$this->app->singleton('Nestor\Repositories\TestSuiteRepository', 'Nestor\Repositories\DbTestSuiteRepository');
		$this->app->singleton('Nestor\Repositories\ExecutionTypeRepository', 'Nestor\Repositories\DbExecutionTypeRepository');
		$this->app->singleton('Nestor\Repositories\TestCaseRepository', 'Nestor\Repositories\DbTestCaseRepository');
		$this->app->singleton('Nestor\Repositories\NavigationTreeRepository', 'Nestor\Repositories\DbNavigationTreeRepository');
		$this->app->singleton('Nestor\Repositories\TestPlanRepository', 'Nestor\Repositories\DbTestPlanRepository');
		$this->app->singleton('Nestor\Repositories\TestRunRepository', 'Nestor\Repositories\DbTestRunRepository');
		$this->app->singleton('Nestor\Repositories\ExecutionStatusRepository', 'Nestor\Repositories\DbExecutionStatusRepository');
		$this->app->singleton('Nestor\Repositories\ExecutionRepository', 'Nestor\Repositories\DbExecutionRepository');

		$this->app->bind('Nestor', function()
		{
			return new \Nestor\Model\Nestor();
		});

		$this->app->bind('PluginManager', function() 
		{
			return new \Nestor\PluginManager();
		});
	}

}