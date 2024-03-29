<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('servers', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->ipAddress('ip');
			$table->string('name');
			$table->timestamps();
			$table->string('super_email');
			$table->string('super_password');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('servers');
	}
}
