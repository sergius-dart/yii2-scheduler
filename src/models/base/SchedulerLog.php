<?php

namespace webtoolsnz\scheduler\models\base;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the base-model class for table "scheduler_log".
 *
 * @property integer $id
 * @property integer $scheduler_task_id
 * @property string $started_at
 * @property string $ended_at
 * @property string $output
 * @property integer $error
 *
 * @property \webtoolsnz\scheduler\models\SchedulerTask $schedulerTask
 */
class SchedulerLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scheduler_log';
    }

    /**
     *
     */
    public static function label($n = 1)
    {
        return Yii::t('app', '{n, plural, =1{Scheduler Log} other{Scheduler Logs}}', ['n' => $n]);
    }

    /**
     *
     */
    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheduler_task_id', 'output'], 'required'],
            [['scheduler_task_id', 'exit_code','scheduler_log_id'], 'integer'],
            [['started_at', 'ended_at'], 'safe'],
            [['output'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scheduler_log_id' => Yii::t('app', 'scheduler_log_id'),
            'scheduler_task_id' => Yii::t('app', 'Scheduler Task ID'),
            'started_at' => Yii::t('app', 'Started At'),
            'ended_at' => Yii::t('app', 'Ended At'),
            'output' => Yii::t('app', 'Output'),
            'exit_code' => Yii::t('app', 'Error'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedulerTask()
    {
        return $this->hasOne(\webtoolsnz\scheduler\models\SchedulerTask::className(), ['id' => 'scheduler_task_id']);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params = null)
    {
        $formName = $this->formName();
        $params = !$params ? Yii::$app->request->get($formName, array()) : $params;
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder'=>['id'=>SORT_DESC]],
        ]);

        $this->load($params, $formName);

        $query->andFilterWhere([
            'id' => $this->id,
            'scheduler_task_id' => $this->scheduler_task_id,
            'exit_code' => $this->exit_code,
        ]);

        $query->andFilterWhere(['like', 'started_at', $this->started_at])
            ->andFilterWhere(['like', 'ended_at', $this->ended_at])
            ->andFilterWhere(['like', 'output', $this->output]);

        return $dataProvider;
    }

    public function getId()
    {
        return $this->scheduler_log_id;
    }
}

