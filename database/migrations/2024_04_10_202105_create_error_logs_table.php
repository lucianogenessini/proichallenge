<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->bigIncrements('id');   
            $table->unsignedBigInteger('request_log_id')->nullable();
            $table->string('exception')->nullable('');
            $table->string('file')->default('');
            $table->integer('line')->nullable();
            $table->text('message')->nullable();
            $table->text('action_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->longText('trace')->nullable();
            $table->auditableWithDeletes();
            
            $table->foreign('request_log_id')->references('id')->on('request_logs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
