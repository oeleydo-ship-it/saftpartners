<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kept apart from site_settings, which is shared with public pages: this table holds secrets.
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mailer')->default('log'); // log | smtp | microsoft365
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('encryption')->nullable(); // tls (STARTTLS) | ssl
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // encrypted
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->string('contact_to')->nullable();
            $table->string('ms_tenant_id')->nullable();
            $table->string('ms_client_id')->nullable();
            $table->text('ms_client_secret')->nullable(); // encrypted
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status')->nullable();
            $table->text('last_test_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
