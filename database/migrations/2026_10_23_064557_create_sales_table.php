
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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->string('customer_name')->nullable();
            $table->string('sale_number')->unique();

            $table->decimal('total', 14, 2)->default(0);

            $table->enum('status',
                  ['pending', 'completed', 'cancelled'])
                  ->default('pending')->index();

            $table->string('payment_method', 20)
                  ->default('tunai');

            $table->timestamp('sold_at')->nullable()->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
