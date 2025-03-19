<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id(); // ID del registro
                $table->unsignedBigInteger('file_id'); // Referencia al archivo afectado
                $table->unsignedBigInteger('user_id'); // Usuario que realizó la acción
                $table->string('action'); // Acción realizada (subir, descargar, editar, etc.)
                $table->timestamp('performed_at'); // Fecha y hora de la acción
                $table->timestamps();

                // Definir claves foráneas
                $table->foreign('file_id')->references('id')->on('archivos')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
