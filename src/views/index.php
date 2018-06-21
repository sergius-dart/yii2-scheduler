<?php
/**
 * Index View for scheduled tasks
 *
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \webtoolsnz\scheduler\models\SchedulerTask $model
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;


$this->title = Yii::t('scheduler','scheduler::title');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="scheduler-index">

    <h1><?= $this->title ?></h1>

    <div class="table-responsive">
        <?php \yii\widgets\Pjax::begin(); ?>
        <?= GridView::widget([
            'layout' => '{summary}{pager}{items}{pager}',
            'dataProvider' => $dataProvider,
            // 'pager' => [
            //     'class' => yii\widgets\LinkPager::className(),
            //     'firstPageLabel' => Yii::t('scheduler', 'scheduler::first'),
            //     'lastPageLabel' => Yii::t('scheduler', 'scheduler::last'),
            // ],
            'columns' => [
                [
                    'attribute' => 'name',
                    'format' => 'html',
                    'value' => function ($t) {
                        return Html::a($t->name, ['update', 'id' => $t->id]);
                    }
                ],

                'name',
                'description',
                'cron',
            ],
        ]); ?>
        <?php \yii\widgets\Pjax::end(); ?>
    </div>
</div>
