<?php


namespace webtoolsnz\scheduler;

use webtoolsnz\scheduler\events\TaskEvent;
use webtoolsnz\scheduler\models\base\SchedulerTask;
use yii\helpers\StringHelper;
use Cron\CronExpression;
use \DateTime;

/**
 * Class Task
 * @package webtoolsnz\scheduler
 */
abstract class Task extends \yii\base\Component
{
    const TASK_FAILED = -1;
    const TASK_SUCCESS = 1;
    const EVENT_BEFORE_RUN = 'TaskBeforeRun';
    const EVENT_AFTER_RUN = 'TaskAfterRun';

    /**
     * @var bool create a database lock to ensure the task only runs once
     */
    static public $databaseLock = true;

    /**
     * Exception raised during run (if any)
     *
     * @var \Exception|null
     */
    public $exception;

    /**
     * Brief description of the task.
     *
     * @var String
     */
    static public $description;

    /**
     * How many seconds after due date to wait until the task becomes overdue and is re-run.
     * This should be set to at least 2x the amount of time the task takes to run as the task will be restarted.
     *
     * @var int
     */
    static public $overdueThreshold = 3600;

    /**
     * @var null|SchedulerTask
     */
    private $_model;

    public function init()
    {
        parent::init();

        $lockName = $this->lockName;
        \yii\base\Event::on(self::className(), self::EVENT_BEFORE_RUN, function ($event) use ($lockName) {
            /* @var $event TaskEvent */
            $db = \Yii::$app->db;
            $result = $db->createCommand("GET_LOCK(:lockname, 1)", [':lockname' => $lockName])->queryScalar();

            if (!$result) {
                // we didn't get the lock which means the task is still running
                $event->cancel = true;
            }
        });
        \yii\base\Event::on(self::className(), self::EVENT_AFTER_RUN, function ($event) use ($lockName) {
            // release the lock
            /* @var $event TaskEvent */
            $db = \Yii::$app->db;
            $db->createCommand("RELEASE_LOCK(:lockname, 1)", [':lockname' => $lockName])->queryScalar();
        });
    }

    /**
     * @param SchedulerTask $model
     */
    public function setModel(SchedulerTask $model)
    {
        $this->_model = $model;
    }
    /**
     * @return SchedulerTask
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * The main method that gets invoked whenever a task is ran, any errors that occur
     * inside this method will be captured by the TaskRunner and logged against the task.
     *
     * @return mixed
     */
    abstract public function run();

    /**
     * @param $str
     */
    public function writeLine($str)
    {
        echo $str.PHP_EOL;
    }

    /**
     * Mark the task as started
     */
    public function start()
    {
        $model = $this->getModel();
        $model->started_at = date('Y-m-d H:i:s');
        $model->save(false);}

    /**
     * Mark the task as stopped.
     */
    public function stop()
    {
        $model = $this->getModel();
        $model->last_run = $model->started_at;
        $model->next_run = $this->getNextRunDate();
        $model->started_at = null;
        $model->save(false);
    }

    /**
     * @param SchedulerTask $model - check model to need run
     * @param bool $forceRun
     * @return bool
     */
    public function shouldRun(SchedulerTask $model, $forceRun = false)
    {
        $prev_run_date = $model->previousRunDate;

        if (!$prev_run_date)
            return $forceRun;

        $due_seconds = (new DateTime() )->getTimestamp() - $prev_run_date->getTimestamp();

        if ( $due_seconds > self::overdueThreshold )
            return $forceRun;

        if ( !$model->active )
            return $forceRun;

        return True;
    }
}
