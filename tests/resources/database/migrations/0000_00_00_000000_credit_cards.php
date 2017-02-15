<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreditCards extends Migration
{

    public function up()
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billable_id');
            $table->string('billable_type');
            $table->string('alias', 100);
            $table->string('number', 10);
            $table->string('token');
            $table->unique(['billable_id', 'billable_type', 'token']);
            $table->string('bank')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_cards');
    }
}
