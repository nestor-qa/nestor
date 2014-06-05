<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestCasesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_cases', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id');
			$table->foreign('project_id')
				->references('id')
				->on('projects')
				->onDelete('cascade');
			$table->integer('test_suite_id');
			$table->foreign('test_suite_id')
				->references('id')
				->on('test_suites')
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
		Schema::drop('test_cases');
	}

}
