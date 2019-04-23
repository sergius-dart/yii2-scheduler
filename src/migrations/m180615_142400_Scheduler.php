<?php
namespace webtoolsnz\scheduler\migrations;
use yii\db\Migration;
/**
 * Class m180615_142400_Scheduler
 */
class m180615_142400_Scheduler extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('scheduler_log', [
            'scheduler_log_id'=> $this->primaryKey(),
            'scheduler_task_id'=> $this->integer(11)->notNull(),
            'started_at'=> $this->timestamp()->notNull()->defaultExpression ('CURRENT_TIMESTAMP'),
            'ended_at'=> $this->timestamp()->defaultValue(null),
            //TODO stdout/stderr?
            'output'=> $this->text(),
            'exit_code'=> $this->integer()
                ->comment('Exit code of "run" function cast to integer!'),
        ]);

        $this->createIndex('fk_table1_scheduler_task_idx', 'scheduler_log','scheduler_task_id',0);

        $this->createTable('scheduler_task', [
            'scheduler_task_id'=> $this->primaryKey(),
            'name'=>$this->string(256)->notNull()
                ->comment('Display userfriendly name'),
            'description'=> $this->text()->notNull()
                ->comment('Too more information for current task'),
            'cron'=> $this->string(45)->notNull()
                ->comment('cron expression'),
            'class_run'=>$this->string(64)->notNull()
                ->comment('class(with namespaces) to create and call "run" implentation'),
            'init_args'=>$this->text()->notNull()
                ->comment('JSON object passed to construct class_run'),
            'last_log_id'=> $this->integer()
                ->comment('Information of last running'), //TODO add foreign key or triggers
            'active'=>  $this->boolean()->notNull()->defaultValue(False)
                ->comment('Need run (ignore from --run-disable)'),
        ]);

        $this->addForeignKey('fk_scheduler_log_scheduler_task_id', 'scheduler_log', 'scheduler_task_id', 'scheduler_task', 'scheduler_task_id','CASCADE','CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // echo "m180615_142400_add_column_shedule_args cannot be reverted.\n";
        $this->delete('scheduler_log');
        $this->delete('scheduler_task');
        $this->dropForeignKey('fk_scheduler_log_scheduler_task_id', 'scheduler_log');
        $this->dropForeignKey('fk_scheduler_task_scheduler_last_log', 'scheduler_task');
        $this->dropTable('scheduler_log');
        $this->dropTable('scheduler_task');
        // return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180615_142400_add_column_shedule_args cannot be reverted.\n";

        return false;
    }
    */
}
