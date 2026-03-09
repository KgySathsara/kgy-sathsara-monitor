<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kgy_sathsara_logs', function (Blueprint $table) {
            $table->id();
            $table->float('cpu_usage')->nullable();
            $table->float('memory_usage')->nullable();
            $table->float('disk_usage')->nullable();
            $table->float('system_load')->nullable();
            $table->boolean('alert_sent')->default(false);
            $table->json('alert_data')->nullable();
            $table->timestamp('checked_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('checked_at');
            $table->index('alert_sent');
            $table->index(['cpu_usage', 'checked_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kgy_sathsara_logs');
    }
};