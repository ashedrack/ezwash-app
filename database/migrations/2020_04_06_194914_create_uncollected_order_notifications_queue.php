<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUncollectedOrderNotificationsQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uncollected_order_notifications_queue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->unique();
            $table->integer('throttle_count')->default(0);
            $table->timestamp('last_mail_sent_at')->nullable();
            $table->string('process_id')->nullable();
            $table->unsignedInteger('status')->comment('1 - Pending, 2 - Processing, 3 - Collected')->default(1);
            $table->text('metadata');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uncollected_order_notifications_queue');
    }

}
