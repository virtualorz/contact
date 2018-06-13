<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContacTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->dateTime('created_at')->comment('建立資料時間');
            $table->dateTime('updated_at')->comment('最後編輯資料時間');
            $table->string('name', 12)->comment('姓名');
            $table->string('company', 24)->comment('公司');
            $table->string('tel', 10)->comment('電話');
            $table->string('email', 386)->comment('email');
            $table->text('message')->comment('留言');
            $table->tinyInteger('status')->unsigned()->comment('狀態 0:未讀 1:已讀 2:已回');
            $table->text('remark')->comment('備註');
            $table->dateTime('delete')->nullable()->comment('刪除時間');
        });
        Schema::create('contact_reply', function (Blueprint $table) {
            $table->bigInteger('contact_id')->unsigned()->comment('聯絡ID');
            $table->integer('id')->unsigned()->comment('回覆ID');
            $table->dateTime('created_at')->comment('建立資料時間');
            $table->dateTime('updated_at')->comment('最後編輯資料時間');
            $table->text('content')->comment('回覆內容');
            $table->integer('creat_admin_id')->unsigned()->nullable()->comment('建立資料管理員ID');
            $table->integer('update_admin_id')->unsigned()->nullable()->comment('最夠更新資料管理員ID');
            $table->primary(['contact_id', 'id']);
        });
        Schema::create('_sn_contact_reply_id', function (Blueprint $table) {
            $table->bigInteger('contact_id')->unsigned()->comment('聯絡ID');
            $table->integer('sn')->unsigned()->comment('目前值');
        });

        $procedure = "
            CREATE DEFINER=`root`@`localhost` PROCEDURE `_get_sn_contact_reply_id`(IN `data_contact_id` integer)
            BEGIN
            
            --
            DECLARE EXIT HANDLER FOR SQLEXCEPTION
            BEGIN
            SHOW ERRORS;
            END;
            
            DECLARE EXIT HANDLER FOR SQLWARNING
            BEGIN
            SHOW WARNINGS;
            END;
            
            --
            SET @tmp_nothing := LAST_INSERT_ID(0);
            UPDATE `_sn_contact_reply_id`
            SET `_sn_contact_reply_id`.`sn` = LAST_INSERT_ID(`_sn_contact_reply_id`.`sn` + 1) 
            WHERE `_sn_contact_reply_id`.`contact_id` = data_contact_id;
            
            --
            SET @data_sn = LAST_INSERT_ID();
            
            --
            IF @data_sn = 0 THEN
            
                --
                INSERT INTO `_sn_contact_reply_id` 
                VALUES(
                    data_contact_id, 
                    IFNULL((
                        SELECT MAX(`contact_reply`.`id`) 
                        FROM `contact_reply` 
                        WHERE `contact_reply`.`contact_id` = data_contact_id
                    ), 0)
                )
                ON DUPLICATE KEY UPDATE `contact_id` = `contact_id`;
            
                --
                UPDATE `_sn_contact_reply_id` 
                SET `_sn_contact_reply_id`.`sn` = LAST_INSERT_ID(`_sn_contact_reply_id`.`sn` + 1) 
                WHERE `_sn_contact_reply_id`.`contact_id` = data_contact_id;
            
                --
                SET @data_sn = LAST_INSERT_ID();
            
            END IF;
            
            SELECT @data_sn AS `sn`;
            
            END";
        DB::unprepared("DROP procedure IF EXISTS _get_sn_contact_reply_id");
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contac');
        Schema::dropIfExists('contact_reply');
    }
}
