<?php
namespace webtoolsnz\scheduler;

use webtoolsnz\scheduler\models\SchedulerLog;
use Yii;
use yii\base\BootstrapInterface;
use webtoolsnz\scheduler\models\SchedulerTask;
use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package webtoolsnz\scheduler
 */
class Scheduler extends \yii\base\Module implements BootstrapInterface
{
    /**
     * Path where task files can be found in the schedulerlication structure.
     * @var string
     */
    public $taskPath = '@scheduler/tasks';

    /**
     * Namespace that tasks use.
     * @var string
     */
    public $taskNameSpace = 'scheduler\tasks';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    /**
     * Bootstrap the console controllers.
     * @param \yii\base\Application $scheduler
     */
    public function bootstrap($scheduler)
    {
        Yii::setAlias('@scheduler', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

        if ($scheduler instanceof \yii\console\Application && !isset($scheduler->controllerMap[$this->id])) {
            $scheduler->controllerMap[$this->id] = [
                'class' => 'webtoolsnz\scheduler\console\SchedulerController',
            ];
        }
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['scheduler'] = [
            'class'          => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            // 'basePath'       => 'webtoolsnz\scheduler\messages'
        ];
    }

    // /**
    //  * Scans the taskPath for any task files, if any are found it attempts to load them,
    //  * creates a new instance of each class and schedulerends it to an array, which it returns.
    //  *
    //  * @return Task[]
    //  * @throws \yii\base\ErrorException
    //  */
    // public function getTasks()
    // {
    //     $dir = Yii::getAlias($this->taskPath);

    //     if (!is_readable($dir)) {
    //         throw new \yii\base\ErrorException("Task directory ($dir) does not exist");
    //     }

    //     $files = array_diff(scandir($dir), array('..', '.'));
    //     $tasks = [];

    //     foreach ($files as $fileName) {
    //         // strip out the file extension to derive the class name
    //         $className = preg_replace('/\.[^.]*$/', '', $fileName);

    //         // validate class name
    //         if (preg_match('/^[a-zA-Z0-9_]*Task$/', $className)) {
    //             $tasks[] = $this->loadTask($className);
    //         }
    //     }

    //     $this->cleanTasks($tasks);

    //     return $tasks;
    // }

    // /**
    //  * Removes any records of tasks that no longer exist.
    //  *
    //  * @param Task[] $tasks
    //  */
    // public function cleanTasks($tasks)
    // {
    //     $currentTasks = ArrayHelper::map($tasks, function ($task) {
    //         return $task->getName();
    //     }, 'description');

    //     foreach (SchedulerTask::find()->indexBy('name')->all() as $name => $task) { /* @var SchedulerTask $task */
    //         if (!array_key_exists($name, $currentTasks)) {
    //             SchedulerLog::deleteAll(['scheduler_task_id' => $task->id]);
    //             $task->delete();
    //         }
    //     }
    // }

    // /**
    //  * Given the className of a task, it will return a new instance of that task.
    //  * If the task doesn't exist, null will be returned.
    //  *
    //  * @param $className
    //  * @return null|object
    //  * @throws \yii\base\InvalidConfigException
    //  */
    // public function loadTask($className)
    // {
    //     $className = implode('\\', [$this->taskNameSpace, $className]);

    //     try {
    //         $task = Yii::createObject($className);
    //         $task->setModel(SchedulerTask::createTaskModel($task));
    //     } catch (\ReflectionException $e) {
    //         $task = null;
    //     }

    //     return $task;
    // }


}
