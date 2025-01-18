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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('stock')->default(1);
            $table->unsignedBigInteger('category_id');
            $table->double('price_unit')->default(0);
            
            $table->foreign('category_id')->references('id')->on('categories');
            $table->unique(['name', 'category_id']);

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
        Schema::dropIfExists('articles');
    }
};
