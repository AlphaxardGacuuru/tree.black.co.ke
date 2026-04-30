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
            $table->foreignUuid('created_by')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('family_tree_user', function (Blueprint $table) {
            $table->foreignUuid('family_tree_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignUuid('user_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->primary(['family_tree_id', 'user_id']);
        });

        Schema::create('family_relationships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('family_tree_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignUuid('user_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignUuid('related_user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('relationship_type');
            $table->timestamps();

            $table->unique([
                'family_tree_id',
                'user_id',
                'related_user_id',
                'relationship_type',
            ], 'family_relationships_unique_edge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_relationships');
        Schema::dropIfExists('family_tree_user');
        Schema::dropIfExists('family_trees');
    }
};
