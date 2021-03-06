<?php


namespace webtoolsnz\scheduler;

use webtoolsnz\scheduler\events\TaskEvent;
use webtoolsnz\scheduler\models\base\SchedulerTask;
use webtoolsnz\scheduler\models\base\SchedulerLog;
use yii\helpers\StringHelper;
use Cron\CronExpression;
use \DateTime;

/**
 * Class Task
 * @package webtoolsnz\scheduler
 */
abstract class Task extends \yii\base\Component
{
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
    static public function name($initArgs)
    {
        
    }

    /**
     * Brief description of the task.
     *
     * @var String
     */
    static public function description($initArgs)
    {
        
    }

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

    // public function init()
    // {
    //     parent::init();

    //     $lockName = $this->lockName;
    //     \yii\base\Event::on(self::className(), self::EVENT_BEFORE_RUN, function ($event) use ($lockName) {
    //         /* @var $event TaskEvent */
    //         $db = \Yii::$app->db;
    //         $result = $db->createCommand("GET_LOCK(:lockname, 1)", [':lockname' => $lockName])->queryScalar();

    //         if (!$result) {
    //             // we didn't get the lock which means the task is still running
    //             $event->cancel = true;
    //         }
    //     });
    //     \yii\base\Event::on(self::className(), self::EVENT_AFTER_RUN, function ($event) use ($lockName) {
    //         // release the lock
    //         /* @var $event TaskEvent */
    //         $db = \Yii::$app->db;
    //         $db->createCommand("RELEASE_LOCK(:lockname, 1)", [':lockname' => $lockName])->queryScalar();
    //     });
    // }

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
        if ( !self::$databaseLock )
            return true;
        /* @var $event TaskEvent */
        $db = \Yii::$app->db;
        $result = $db->createCommand("SELECT GET_LOCK(:lockname, 1) as `lock`", [':lockname' => $this->lockName])->queryScalar();

        return !!$result;
    }

    /**
     * Mark the task as stopped.
     */
    public function stop()
    {
        // release the lock
        /* @var $event TaskEvent */
        if ( !self::$databaseLock )
            return true;
        $db = \Yii::$app->db;
        $db->createCommand("SELECT RELEASE_LOCK(:lockname) as `lock`", [':lockname' => $this->lockName])->queryScalar();
    }

    /**
     * @param SchedulerTask $model - check model to need run
     * @param bool $forceRun
     * @return bool
     */
    static public function shouldRun(SchedulerTask $model, $forceRun = false)
    {
        $prev_run_date = $model->previousRunDate;

        if (!$prev_run_date)
            return $forceRun;

        $due_seconds = (new DateTime() )->getTimestamp() - $prev_run_date->getTimestamp();

        if ( $due_seconds > self::$overdueThreshold )
            return $forceRun;

        if ( !$model->active )
            return $forceRun;

        //if NOT found log - need to run
        if ( !$model->lastLog )
            return true;

        //if last log too late
        $due_last = ( new DateTime() )->getTimestamp() - (new DateTime( $model->lastLog->started_at ) )->getTimestamp();
        if ( $due_last > self::$overdueThreshold )
            return $forceRun;

        // if task last log is complete
        if ( SchedulerLog::TASK_COMPLETE == $model->lastLog->status)
            return $forceRun;

        return True;
    }

    public function getLockName()
    {
        return $this->model->lockName;
    }

    public function lockTable()
    {
        return TRUE;
    }

}
