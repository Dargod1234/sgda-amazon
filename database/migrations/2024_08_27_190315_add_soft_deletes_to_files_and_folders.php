<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('archivos', function (Blueprint $table) {
            if (!Schema::hasColumn('archivos', 'deleted_at')) {
                $table->softDeletes(); // Añade la columna deleted_at a la tabla archivos si no existe
            }
            
            if (!Schema::hasColumn('archivos', 'user_dropper_id')) {
                $table->unsignedBigInteger('user_dropper_id')->nullable(); // Añade la columna movido_a_papelera_por
            }
        });

        Schema::table('carpetas', function (Blueprint $table) {
            if (!Schema::hasColumn('carpetas', 'deleted_at')) {
                $table->softDeletes(); // Añade la columna deleted_at a la tabla carpetas si no existe
            }
            
            if (!Schema::hasColumn('carpetas', 'user_dropper_id')) {
                $table->unsignedBigInteger('user_dropper_id')->nullable(); // Añade la columna movido_a_papelera_por
            }
        });
    }

    public function down()
    {
        Schema::table('archivos', function (Blueprint $table) {
            if (Schema::hasColumn('archivos', 'deleted_at')) {
                $table->dropSoftDeletes(); // Elimina la columna deleted_at de la tabla archivos
            }

            if (Schema::hasColumn('archivos', 'user_dropper_id')) {
                $table->dropColumn('user_dropper_id'); // Elimina la columna movido_a_papelera_por
            }
        });

        Schema::table('carpetas', function (Blueprint $table) {
            if (Schema::hasColumn('carpetas', 'deleted_at')) {
                $table->dropSoftDeletes(); // Elimina la columna deleted_at de la tabla carpetas
            }

            if (Schema::hasColumn('carpetas', 'user_dropper_id')) {
                $table->dropColumn('user_dropper_id'); // Elimina la columna movido_a_papelera_por
            }
        });
    }
};
