<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Billables extends Migration
{

    public function up()
    {
        Schema::create('billables', function (Blueprint $table) {
            $table->unsignedInteger('billable_id');
            $table->string('billable_type');
            $table->primary(['billable_id', 'billable_type']);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('identity_number');
            $table->text('shipping_address');
            $table->text('billing_address');
            $table->string('mobile_number');
            $table->string('iyzipay_key')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('billables');
    }
}
