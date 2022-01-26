<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrderTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_types', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 100)->nullable();
		});
	}

//CREATE TABLE IF NOT EXISTS `ezwash_v2`.`orders` (
//`id` INT NOT NULL AUTO_INCREMENT,
//`user_id` INT NOT NULL,
//`order_type` INT NULL,
//`services` TEXT(65535) NULL,
//`status` INT(4) NOT NULL,
//`amount` INT(11) NOT NULL DEFAULT 0,
//`payment_method` INT(4) NULL,
//`created_by` INT(11) NOT NULL,
//`location_id` INT(5) NOT NULL,
//`collected` TINYINT(2) NOT NULL DEFAULT 0,
//`note` TEXT(65535) NULL,
//`bags` INT(11) NULL DEFAULT NULL,
//PRIMARY KEY (`id`),
//INDEX `order_type_fk_idx` (`order_type` ASC) VISIBLE,
//INDEX `order_statuses_idx` (`status` ASC) VISIBLE,
//INDEX `payment_method_idx` (`payment_method` ASC) VISIBLE,
//INDEX `user_id_idx` (`user_id` ASC) VISIBLE,
//INDEX `collected_idx` (`collected` ASC) VISIBLE,
//CONSTRAINT `order_type_fk`
//FOREIGN KEY (`order_type`)
//REFERENCES `ezwash_v2`.`order_types` (`id`)
//ON DELETE NO ACTION
//ON UPDATE NO ACTION,
//CONSTRAINT `order_statuses`
//FOREIGN KEY (`status`)
//REFERENCES `ezwash_v2`.`orders_statuses` (`id`)
//ON DELETE NO ACTION
//ON UPDATE NO ACTION,
//CONSTRAINT `payment_method`
//FOREIGN KEY (`payment_method`)
//REFERENCES `ezwash_v2`.`payment_methods` (`id`)
//ON DELETE NO ACTION
//ON UPDATE NO ACTION,
//CONSTRAINT `user_id`
//FOREIGN KEY (`user_id`)
//REFERENCES `ezwash_v2`.`users` (`id`)
//ON DELETE NO ACTION
//ON UPDATE NO ACTION)
//ENGINE = InnoDB
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('order_types');
	}

}
