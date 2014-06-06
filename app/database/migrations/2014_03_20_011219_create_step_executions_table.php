<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStepExecutionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('step_executions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('execution_id');
			$table->foreign('execution_id')
				->references('id')
				->on('executions')
				->onDelete('cascade');
			$table->integer('test_case_step_version_id');
			$table->foreign('test_case_step_version_id')
				->references('id')
				->on('test_case_step_versions')
				->onDelete('cascade');
			$table->integer('execution_status_id')->default(1);
			$table->foreign('execution_status_id')
				->references('id')
				->on('executions_statuses');
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
		Schema::drop('step_executions');
	}

}
