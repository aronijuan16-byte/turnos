<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('prefix', 3)->unique();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->unsignedInteger('number');
            $t->string('code', 10);
            $t->string('citizen_name');
            $t->string('document', 20);
            $t->enum('status', ['waiting', 'calling', 'serving', 'done', 'absent'])->default('waiting');
            $t->string('window', 30)->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('called_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->timestamps();
            $t->unique(['service_id', 'date', 'number']);
            $t->index(['status', 'date']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('services');
    }
};
