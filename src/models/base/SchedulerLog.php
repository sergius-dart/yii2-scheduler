<?php

namespace webtoolsnz\scheduler\models\base;

use Yii;
use yii\data\ActiveDataProvider;
use webtoolsnz\scheduler\models\base\SchedulerTask;

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
    const TASK_COMPLETE = 1;
    const TASK_ERROR = -1;
    const TASK_RUNNING = 0;
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
            [['scheduler_task_id'], 'required'],
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
            'scheduler_log_id' => Yii::t('app', 'SchedulerLog::scheduler_log_id'),
            'scheduler_task_id' => Yii::t('app', 'SchedulerLog::scheduler_task_id'),
            'started_at' => Yii::t('app', 'SchedulerLog::started_at'),
            'ended_at' => Yii::t('app', 'SchedulerLog::ended_at'),
            'output' => Yii::t('app', 'SchedulerLog::output'),
            'exit_code' => Yii::t('app', 'SchedulerLog::exit_code'),
            'duration' => Yii::t('app', 'SchedulerLog::duration'),
            'status' => Yii::t('app', 'SchedulerLog::status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedulerTask()
    {
        return $this->hasOne(\webtoolsnz\scheduler\models\base\SchedulerTask::className(), ['scheduler_task_id' => 'scheduler_task_id']);
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
            'sort' => ['defaultOrder'=>['scheduler_log_id'=>SORT_DESC]],
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

    public function getStatus()
    {
        if ( $this->started_at && !$this->ended_at)
            return self::TASK_RUNNING;
        if ( is_null( $this->exit_code) )
            return self::TASK_RUNNING;
        if ( $this->exit_code >= 0 )
            return self::TASK_COMPLETE;
        return self::TASK_ERROR;
    }

    public function getDuration()
    {
        $start = new \DateTime($this->started_at);
        $end = new \DateTime($this->ended_at);
        $diff = $start->diff($end);
        return $diff->format('%hh %im %Ss');
    }
}

