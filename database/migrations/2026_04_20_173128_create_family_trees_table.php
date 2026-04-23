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
        Schema::create('family_trees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('created_by')->unique();
            $table->string('name');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('family_tree_user', function (Blueprint $table) {
            $table->uuid('family_tree_id');
            $table->uuid('user_id')->unique();
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->primary(['family_tree_id', 'user_id']);

            $table->foreign('family_tree_id')->references('id')->on('family_trees')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('family_relationships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('family_tree_id')->index();
            $table->uuid('user_id')->index();
            $table->uuid('related_user_id')->index();
            $table->string('relationship_type');
            $table->timestamps();

            $table->unique(['family_tree_id', 'user_id', 'related_user_id', 'relationship_type'], 'family_relationships_unique_edge');

            $table->foreign('family_tree_id')->references('id')->on('family_trees')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('related_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('family_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('family_tree_id')->index();
            $table->uuid('inviter_id')->index();
            $table->uuid('invited_user_id')->nullable()->index();
            $table->string('invitee_email')->nullable()->index();
            $table->string('relationship_type');
            $table->string('token')->unique();
            $table->string('status')->default('pending')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->foreign('family_tree_id')->references('id')->on('family_trees')->cascadeOnDelete();
            $table->foreign('inviter_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('invited_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_invitations');
        Schema::dropIfExists('family_relationships');
        Schema::dropIfExists('family_tree_user');
        Schema::dropIfExists('family_trees');
    }
};
