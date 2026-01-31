<?php

use Eventsourcerer\EventSourcererLaravel\Repository\CacheWorkerEvents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(CacheWorkerEvents::STORE_TABLE, static function (Blueprint $table) {
            $table->id();
            $table->string('workerId')->index();
            $table->json('payload');
            $table->integer('allSequence');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CacheWorkerEvents::STORE_TABLE);
    }
};
