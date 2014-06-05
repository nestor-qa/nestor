<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestPlansTestCasesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_plans_test_cases', function(Blueprint $table)
		{
			$table->integer('test_plan_id');
			$table->foreign('test_plan_id')->references('id')->on('test_plans');
			$table->integer('test_case_version_id');
			$table->foreign('test_case_version_id')->references('id')->on('test_case_versions');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('test_plans_test_cases');
	}

}
