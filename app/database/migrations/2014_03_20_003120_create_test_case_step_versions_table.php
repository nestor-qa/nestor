<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestCaseStepVersionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_case_step_versions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('version');
			$table->integer('order');
			$table->string('description', 500);
			$table->string('expected_result', 500);
			$table->integer('test_case_version_id');
			$table->foreign('test_case_version_id')
				->references('id')
				->on('test_case_versions')
				->onDelete('cascade');
			$table->integer('test_case_step_id');
			$table->foreign('test_case_step_id')
				->references('id')
				->on('test_case_steps')
				->onDelete('cascade');
			$table->integer('execution_status_id');
			$table->foreign('execution_status_id')
				->references('id')
				->on('execution_types')
				->onDelete('cascade');
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
		Schema::drop('test_case_step_versions');
	}

}
