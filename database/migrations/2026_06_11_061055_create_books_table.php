<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. 'unu-001'
            $table->string('title');
            $table->string('author');
            $table->string('genre');
            $table->text('description');
            $table->integer('stock');
            $table->integer('total_stock');
            $table->integer('published_year');
            $table->integer('popularity')->default(50);
            $table->text('cover_style'); // Will hold JSON string config
            $table->string('image_path')->nullable(); // Uploaded cover image path
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
        Schema::dropIfExists('books');
    }
}
