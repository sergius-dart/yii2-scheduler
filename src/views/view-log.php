<?php
/**
 * Update Task View
 *
 * @var yii\web\View $this
 * @var webtoolsnz\scheduler\models\SchedulerLog $model
 */

use yii\helpers\Html;
use webtoolsnz\scheduler\models\SchedulerTask;


$this->title = $model->__toString();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app','scheduler::title'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->schedulerTask->__toString(), 'url' => ['update', 'id' => $model->scheduler_task_id]];
$this->params['breadcrumbs'][] = $model->__toString();
?>

<div class="">

    <h1><?=$this->title ?></h1>

    <div class="well">
        <dl class="dl-horizontal">
            <dt>Description</dt>
            <dd><?= Html::encode($model->schedulerTask->description) ?></dd>

            <dt><?= $model->getAttributeLabel('started_at') ?></dt>
            <dd><?= Yii::$app->formatter->asDatetime($model->started_at) ?></dd>

            <dt><?= $model->getAttributeLabel('ended_at') ?></dt>
            <dd><?= Yii::$app->formatter->asDatetime($model->ended_at) ?></dd>

            <dt><?= $model->getAttributeLabel('duration') ?></dt>
            <dd><?= $model->duration ?></dd>

            <dt><?= $model->getAttributeLabel('status') ?></dt>
            <dd>
                <?php if (is_null($model->exit_code) || $model->exit_code < 0 ): ?>
                    <span class="text-danger glyphicon glyphicon-remove-circle"></span> Error
                <?php else: ?>
                    <span class="text-success glyphicon glyphicon-ok-circle"></span> Success
                <?php endif ?>
            </dd>
        </dl>

        <h3> <?= $model->getAttributeLabel('output') ?> </h3>
        <textarea class="form-control" rows="7"><?= $model->output ?></textarea>
    </div>
</div>
