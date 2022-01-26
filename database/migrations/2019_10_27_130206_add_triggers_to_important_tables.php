<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTriggersToImportantTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS post_company_update');
        //Companies update and delete trigger
        DB::unprepared('CREATE TRIGGER post_company_update AFTER UPDATE ON `companies` FOR EACH ROW
                BEGIN
                   INSERT INTO `companies_revs` (`id`, `name`, `owner_id`, `description`, `is_active`, `ps_live_secret`, `ps_test_secret`, `created_at`, `updated_at`,`deleted_at`, `action`) 
                   VALUES (OLD.id, OLD.name, OLD.owner_id, OLD.description, OLD.is_active, OLD.ps_live_secret, OLD.ps_test_secret, OLD.created_at, OLD.updated_at, OLD.deleted_at, "update");
                END');

        DB::unprepared('DROP TRIGGER IF EXISTS post_company_delete');
        DB::unprepared('CREATE TRIGGER post_company_delete AFTER DELETE ON `companies` FOR EACH ROW
                BEGIN
                   INSERT INTO `companies_revs` (`id`, `name`, `owner_id`, `description`, `is_active`, `ps_live_secret`, `ps_test_secret`, `created_at`, `updated_at`,`deleted_at`, `action`) 
                   VALUES (OLD.id, OLD.name, OLD.owner_id, OLD.description, OLD.is_active, OLD.ps_live_secret, OLD.ps_test_secret, OLD.created_at, OLD.updated_at, OLD.deleted_at, "delete");
                END');

        DB::unprepared('DROP TRIGGER IF EXISTS post_employee_update');
        DB::unprepared('CREATE TRIGGER post_employee_update AFTER UPDATE ON `employees` FOR EACH ROW
                BEGIN
                   INSERT INTO `employees_revs` (`id`, `email`, `name`, `phone`, `gender`, `avatar`, `password`, `created_by`, `location_on_create`, `location_id`, `company_id`,`created_at`, `updated_at`,`deleted_at`, `action`) 
                   VALUES (OLD.id, OLD.email, OLD.name, OLD.phone, OLD.gender, OLD.avatar, OLD.password, OLD.created_by, OLD.location_on_create, OLD.location_id, OLD.company_id, OLD.created_at, OLD.updated_at, OLD.deleted_at, "update");
                END');

        DB::unprepared('DROP TRIGGER IF EXISTS post_employee_delete');
        DB::unprepared('CREATE TRIGGER post_employee_delete AFTER DELETE ON `employees` FOR EACH ROW
                BEGIN
                   INSERT INTO `employees_revs` (`id`, `email`, `name`, `phone`, `gender`, `avatar`, `password`, `created_by`, `location_on_create`, `location_id`, `company_id`,`created_at`, `updated_at`,`deleted_at`, `action`) 
                   VALUES (OLD.id, OLD.email, OLD.name, OLD.phone, OLD.gender, OLD.avatar, OLD.password, OLD.created_by, OLD.location_on_create, OLD.location_id, OLD.company_id, OLD.created_at, OLD.updated_at, OLD.deleted_at, "delete");
                END');

        DB::unprepared('DROP TRIGGER IF EXISTS post_location_update');
        DB::unprepared('CREATE TRIGGER post_location_update AFTER UPDATE ON `locations` FOR EACH ROW
                BEGIN
                   INSERT INTO `locations_revs` (`id`,`name`,`address`,`phone`,`store_image`,`number_of_lockers`,`company_id`,`created_at`,`updated_at`, `deleted_at`, `action`) 
                   VALUES (OLD.id, OLD.name, OLD.address, OLD.phone, OLD.store_image, OLD.number_of_lockers, OLD.company_id, OLD.created_at, OLD.updated_at, OLD.deleted_at, "update");
                END');
        DB::unprepared('DROP TRIGGER IF EXISTS post_location_delete');
        DB::unprepared('CREATE TRIGGER post_location_delete AFTER DELETE ON `locations` FOR EACH ROW
                BEGIN
                   INSERT INTO `locations_revs` (`id`,`name`,`address`,`phone`,`store_image`,`number_of_lockers`,`company_id`,`created_at`,`updated_at`, `deleted_at`, `action`) 
                   VALUES (OLD.id, OLD.name, OLD.address, OLD.phone, OLD.store_image, OLD.number_of_lockers, OLD.company_id, OLD.created_at, OLD.updated_at, OLD.deleted_at, "delete");
                END');

        //Orders update and delete trigger
        DB::unprepared('DROP TRIGGER IF EXISTS post_order_update');
        DB::unprepared('CREATE TRIGGER post_order_update AFTER UPDATE ON `orders` FOR EACH ROW
                BEGIN
                   INSERT INTO `orders_revs` (`id`,`user_id`,`order_type`,`status`,`amount`,`payment_method`,`created_by`,`location_id`,`company_id`,`collected`,`note`,`bags`, `created_at`,`updated_at`, `deleted_at`, `action`) 
                   VALUES (OLD.id, OLD.user_id, OLD.order_type, OLD.status, OLD.amount, OLD.payment_method, OLD.created_by, OLD.location_id, OLD.company_id, OLD.collected, OLD.note, OLD.bags, OLD.created_at, OLD.updated_at, OLD.deleted_at, "update");
                END');
        DB::unprepared('CREATE TRIGGER post_order_delete AFTER DELETE ON `orders` FOR EACH ROW
                BEGIN
                   INSERT INTO `orders_revs` (`id`,`user_id`,`order_type`,`status`,`amount`,`payment_method`,`created_by`,`location_id`,`company_id`,`collected`,`note`,`bags`, `created_at`,`updated_at`, `deleted_at`, `action`) 
                   VALUES (OLD.id, OLD.user_id, OLD.order_type, OLD.status, OLD.amount, OLD.payment_method, OLD.created_by, OLD.location_id, OLD.company_id, OLD.collected, OLD.note, OLD.bags, OLD.created_at, OLD.updated_at, OLD.deleted_at, "delete");
                END');


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER `post_company_update`');
        DB::unprepared('DROP TRIGGER `post_company_delete`');
        DB::unprepared('DROP TRIGGER `post_employee_update`');
        DB::unprepared('DROP TRIGGER `post_employee_delete`');
        DB::unprepared('DROP TRIGGER `post_location_update`');
        DB::unprepared('DROP TRIGGER `post_location_delete`');
        DB::unprepared('DROP TRIGGER `post_order_update`');
        DB::unprepared('DROP TRIGGER `post_order_delete`');
    }
}
