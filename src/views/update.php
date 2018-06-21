<?php
/**
 * Update Task View
 *
 * @var yii\web\View $this
 * @var webtoolsnz\scheduler\models\SchedulerTask $model
 */

use yii\helpers\Html;
use webtoolsnz\scheduler\models\SchedulerTask;
use yii\bootstrap\Tabs;
use yii\bootstrap\ActiveForm;
use webtoolsnz\widgets\RadioButtonGroup;
use yii\grid\GridView;


$this->title = $model->__toString();
$this->params['breadcrumbs'][] = ['label' => Yii::t('scheduler','scheduler::title'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->__toString();
?>
<div class="task-update">

    <h1><?=$this->title ?></h1>

    <?php $this->beginBlock('main'); ?>
    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= $form->field($model, 'name', ['inputOptions' => ['disabled' => 'disabled']]) ?>
    <?= $form->field($model, 'description', ['inputOptions' => ['disabled' => 'disabled']]) ?>
    <?= $form->field($model, 'cron', ['inputOptions' => ['disabled' => 'disabled']]) ?>
    
    <?= $form->field($model, 'active')->widget(RadioButtonGroup::className(), [
        'items' => [1 => Yii::t( 'scheduler', 'scheduler::update::active::yes'), 0 => Yii::t( 'scheduler', 'scheduler::update::active::no')],
        'itemOptions' => [
            'buttons' => [0 => ['activeState' => 'btn active btn-danger']]
        ]
    ]); ?>

    <?= Html::submitButton('<span class="glyphicon glyphicon-check"></span> ' . ($model->isNewRecord ? Yii::t('scheduler', 'scheduler::update::create') : Yii::t('scheduler', 'scheduler::update::save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-primary'
    ]); ?>

    <?php ActiveForm::end(); ?>
    <?php $this->endBlock(); ?>



    <?php $this->beginBlock('logs'); ?>
    <div class="table-responsive">
        <?php \yii\widgets\Pjax::begin(['id' => 'logs']); ?>
        <?= GridView::widget([
            'layout' => '{summary}{pager}{items}{pager}',
            'dataProvider' => $logDataProvider,
            // 'pager' => [
            //     'class' => yii\widgets\LinkPager::className(),
            //     'firstPageLabel' => Yii::t('scheduler', 'First'),
            //     'lastPageLabel' => Yii::t('scheduler', 'Last'),
            // ],
            'columns' => [
                [
                    'attribute' => 'started_at',
                    'format' => 'raw',
                    'value' => function ($m) {
                        return Html::a(Yii::$app->getFormatter()->asDatetime($m->started_at), ['view-log', 'id' => $m->id]);
                    }
                ],
                'ended_at:datetime',
                [
                    'attribute' => 'duration',
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        return Html::tag('span', '', [
                            'class' => (is_null($model->exit_code) || $model->exit_code < 0 )  ? 'text-danger glyphicon glyphicon-remove-circle' : 'text-success glyphicon glyphicon-ok-circle'
                        ]);
                    }
                ],
            ],
        ]); ?>
        <?php \yii\widgets\Pjax::end(); ?>
    </div>
    <?php $this->endBlock(); ?>

    <?= Tabs::widget([
        'encodeLabels' => false,
        'id' => 'customer',
        'items' => [
            'overview' => [
                'label'   => Yii::t('scheduler', 'scheduler::overview'),
                'content' => $this->blocks['main'],
                'active'  => true,
            ],
            'logs' => [
                'label' => Yii::t('scheduler','scheduler::logs'),
                'content' => $this->blocks['logs'],
            ],
        ]
    ]);?>
</div>
