
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 30)->unique();
            $table->string('status', 20)->default('active');
            $table->string('role', 20)->default('user');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone_number']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone_number',
                'status',
                'role',
            ]);
        });
    }
};
