<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_id')->nullable()->after('total_before_tax');
            $table->text('shipping_details')->nullable()->after('final_total');
            $table->decimal('shipping_charges', 22, 4)->default(0)->after('shipping_details');
            $table->decimal('exchange_rate', 22, 4)->default(1)->after('final_total');
            $table->integer('pay_term_number')->nullable()->after('payment_status');
            $table->string('pay_term_type')->nullable()->after('pay_term_number');
            $table->string('document')->nullable()->after('transaction_note');
            $table->text('additional_notes')->nullable()->after('document');
            $table->text('staff_note')->nullable()->after('additional_notes');
            $table->string('ref_no')->nullable()->change(); // Ensure nullable
            
            // For purchase order
            $table->integer('import_batch')->nullable();
            $table->integer('import_licence_number')->nullable();
            
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'tax_id', 
                'shipping_details', 
                'shipping_charges', 
                'exchange_rate', 
                'pay_term_number', 
                'pay_term_type',
                'document',
                'additional_notes',
                'staff_note',
                'import_batch',
                'import_licence_number'
            ]);
        });
    }
};
