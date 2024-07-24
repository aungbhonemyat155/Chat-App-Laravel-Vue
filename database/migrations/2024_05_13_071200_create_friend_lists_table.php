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
        Schema::create('friend_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("first_user_id");
            $table->unsignedBigInteger("second_user_id");
            $table->boolean("is_approve")->default(false);
            $table->boolean("is_delete")->default(false);
            $table->text("last_message")->nullable();
            $table->timestamps();

            $table->foreign('first_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('second_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friend_lists');
    }
};
