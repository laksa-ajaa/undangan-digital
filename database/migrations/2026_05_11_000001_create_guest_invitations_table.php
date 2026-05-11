<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_invitations', function (Blueprint $table): void {
            $table->id();
            $table->string('guest_name');
            $table->string('share_code', 26)->unique();
            $table->text('share_message')->nullable();
            $table->text('share_link')->nullable();
            $table->string('shared_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_invitations');
    }
};
