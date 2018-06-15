<?php

use yii\db\Migration;

/**
 * Class m180615_142400_add_column_shedule_args
 */
class m180615_142400_add_column_shedule_args extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'scheduler_task',
            'args',
            $this->text()->notNull()->defaultValue('')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // echo "m180615_142400_add_column_shedule_args cannot be reverted.\n";
        $this->dropColumn('scheduler_task', 'args');
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
