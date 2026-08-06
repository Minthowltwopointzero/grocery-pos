<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name'); // snapshot of the name at the time, in case the account is later deleted
            $table->string('user_role')->nullable();
            $table->string('action'); // e.g. login, logout, product_created, product_updated, product_deleted, customer_created, ...
            $table->text('description'); // human-readable summary, e.g. "Product deleted: Lucky Me Pancit Canton (barcode 17579)"
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
