<?php
namespace webtoolsnz\scheduler\actions;

use webtoolsnz\scheduler\models\base\SchedulerLog;
use Yii;
use yii\base\Action;
use webtoolsnz\scheduler\models\base\SchedulerTask;

/**
 * Class UpdateAction
 * @package webtoolsnz\scheduler\actions
 */
class DeleteTaskAction extends Action
{
    /**
     * @var string the view file to be rendered. If not set, it will take the value of [[id]].
     * That means, if you name the action as "index" in "SchedulerController", then the view name
     * would be "index", and the corresponding view file would be "views/scheduler/index.php".
     */
    public $view;

    /**
     * Runs the action
     *
     * @return string result content
     */
    public function run($id)
    {
        $model = SchedulerTask::findOne($id);

        if (!$model) {
            throw new \yii\web\HttpException(404, 'The requested page does not exist.');
        }

        $model->delete(); //all logs autoclear by foreign key
        return $this->controller->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
}
