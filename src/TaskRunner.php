<?php
namespace webtoolsnz\scheduler;

use Yii;
use webtoolsnz\scheduler\events\TaskEvent;
use webtoolsnz\scheduler\models\base\SchedulerLog;
use webtoolsnz\scheduler\models\base\SchedulerTask;
use \DateTime;

/**
 * Class TaskRunner
 *
 * @package webtoolsnz\scheduler
 * @property \webtoolsnz\scheduler\Task $task
 */
class TaskRunner extends \yii\base\Component
{

    /**
     * Indicates whether an error occured during the executing of the task.
     * @var bool
     */
    public $error;

    /**
     * The task that will be executed.
     *
     * @var \webtoolsnz\scheduler\Task
     */
    private $_task;

    /**
     * @var \webtoolsnz\scheduler\models\SchedulerLog
     */
    private $_log;

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @param Task $task
     */
    public function setTask(SchedulerTask $task)
    {
        $this->_task = $task;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->_task;
    }

    /**
     * @param \webtoolsnz\scheduler\models\SchedulerLog $log
     */
    public function setLog($log)
    {
        $this->_log = $log;
    }

    /**
     * @return SchedulerLog
     */
    public function getLog()
    {
        return $this->_log;
    }

    /**
     * @param bool $forceRun
     */
    public function runTask($forceRun = false)
    {
        $task = $this->getTask();
        $log_obj = $this->log;
        $log_obj->started_at =  (new DateTime())->format('Y-m-d H:i:s');

        $raised_exception = null;
        $task_obj = null;

        ob_start();
        try{
            $cl_name = $task->class_run;

            if ( $cl_name::shouldRun($task,$forceRun) )
            {
                $task_obj = new $cl_name( array_merge( $task->initArgs, ['model'=>$task] )) ;
                if (!$task_obj->start()) 
                    //cancel ( locked table?)
                    return;

                $log_obj->exit_code = $task_obj->run();
            }
        } catch (\Throwable $e) {
            $log_obj->exit_code = -1; //set exit code to save log
            if ( !( $e instanceof \Exception ) )
                $log_obj->exit_code = -2;//set exit code to save log
            $raised_exception = $e;
        } finally {
            if ( $raised_exception )
                $this->handleError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());

            $output = ob_get_contents(); //get output ( mb not save - but ob_start is called - need ob_end_clean )
            ob_end_clean();

            if ( !is_null($log_obj->exit_code) ) //if call run - need ob_end_clean and run trigger
            {
                $log_obj->output = $output;
                
                if ( $task_obj)
                    $task_obj->stop();

                $log_obj->ended_at = (new DateTime())->format('Y-m-d H:i:s');
                $log_obj->link( 'schedulerTask', $task );
            }
            return $raised_exception;
        }
    }

    /**
     * If the yii error handler has been overridden with `\webtoolsnz\scheduler\ErrorHandler`,
     * pass it this instance of TaskRunner, so it can update the state of tasks in the event of a fatal error.
     */
    public function shutdownHandler()
    {
        $errorHandler = Yii::$app->getErrorHandler();

        if ($errorHandler instanceof \webtoolsnz\scheduler\ErrorHandler) {
            Yii::$app->getErrorHandler()->taskRunner = $this;
        }
    }

    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $lineNumber
     */
    public function handleError($code, $message, $file, $lineNumber)
    {
        echo sprintf('ERROR: %s %s', $code, PHP_EOL);
        echo sprintf('ERROR FILE: %s %s', $file, PHP_EOL);
        echo sprintf('ERROR LINE: %s %s', $lineNumber, PHP_EOL);
        echo sprintf('ERROR MESSAGE: %s %s', $message, PHP_EOL);

        // if the failed task was mid transaction, rollback so we can save.
        if (null !== ($tx = \Yii::$app->db->getTransaction())) {
            $tx->rollBack();
        }
    }
}
