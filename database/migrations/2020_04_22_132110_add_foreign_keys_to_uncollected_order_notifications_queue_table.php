<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToUncollectedOrderNotificationsQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uncollected_order_notifications_queue', function (Blueprint $table) {
            $table->foreign('order_id', 'order_to_queue_fk')->references('id')
                ->on('orders')->onUpdate('NO ACTION')->onDelete('CASCADE');

            $table->foreign('status', 'processing_status_fk')->references('id')
                ->on('uncollected_order_notification_statuses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('uncollected_order_notifications_queue', function (Blueprint $table) {
            $table->dropForeign('order_to_queue_fk');
            $table->dropForeign('processing_status_fk');
        });
    }
}
