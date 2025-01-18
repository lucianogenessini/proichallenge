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
        Schema::create('request_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('uri')->nullable();
            $table->string('friendly_name')->nullable();
            $table->string('method')->nullable();
            $table->longText('body')->nullable();
            $table->string('endpoint')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_full_name')->nullable();
            $table->integer('response_status')->nullable();
            $table->longText('user_agent')->nullable();

            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
            $table->auditableWithDeletes();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
