<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Personal details
            $table->string('prefix')->nullable()->after('name');
            $table->date('date_of_birth')->nullable()->after('avatar_url');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('blood_group')->nullable()->after('marital_status');
            $table->string('mobile_number')->nullable()->after('blood_group');
            $table->string('guardian_name')->nullable()->after('mobile_number');
            $table->string('nationality')->nullable()->after('guardian_name');
            $table->string('national_id_number')->nullable()->after('nationality');
            $table->text('address')->nullable()->after('national_id_number');

            // Bank details
            $table->string('bank_account_holder_name')->nullable()->after('address');
            $table->string('bank_account_number')->nullable()->after('bank_account_holder_name');
            $table->string('bank_name')->nullable()->after('bank_account_number');
            $table->string('bank_identifier_code')->nullable()->after('bank_name');
            $table->string('bank_branch')->nullable()->after('bank_identifier_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'prefix',
                'date_of_birth',
                'gender',
                'marital_status',
                'blood_group',
                'mobile_number',
                'guardian_name',
                'nationality',
                'national_id_number',
                'address',
                'bank_account_holder_name',
                'bank_account_number',
                'bank_name',
                'bank_identifier_code',
                'bank_branch',
            ]);
        });
    }
};
