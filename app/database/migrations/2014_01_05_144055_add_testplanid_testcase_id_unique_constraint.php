<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTestplanidTestcaseIdUniqueConstraint extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('test_plans_test_cases', function(Blueprint $table)
		{
			$table->unique(array('test_plan_id', 'test_case_version_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('test_plans_test_cases', function(Blueprint $table)
		{
			$table->dropUnique(array('test_plan_id', 'test_case_version_id'));
		});
	}

}