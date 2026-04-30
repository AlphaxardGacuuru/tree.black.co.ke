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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique()->index();
            $table->uuid('family_tree_id');
            $table->uuid('inviter_id');
            $table->string('relationship_type');
            $table->uuid('used_by_id')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->foreign('family_tree_id')->references('id')->on('family_trees')->cascadeOnDelete();
            $table->foreign('inviter_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('used_by_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
