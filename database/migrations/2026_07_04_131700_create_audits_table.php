<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('auditable_table');
            $table->string('action', 32);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('audited_at')->useCurrent();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id'], 'audits_auditable_index');
            $table->index(['auditable_table', 'auditable_id'], 'audits_table_record_index');
            $table->index(['user_id', 'audited_at'], 'audits_user_audited_at_index');
            $table->index(['action', 'audited_at'], 'audits_action_audited_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
