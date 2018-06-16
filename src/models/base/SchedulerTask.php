<?php

namespace webtoolsnz\scheduler\models\base;

use Yii;
use yii\data\ActiveDataProvider;
use Cron\CronExpression;
use webtoolsnz\scheduler\models\base\SchedulerLog;

/**
 * This is the base-model class for table "scheduler_task".
 *
 * @property integer $id
 * @property string $name
 * @property string $crom
 * @property string $description
 * @property string $class_run
 * @property string $init_args
 * @property integer $last_log_id
 * @property integer $active
 *
 * @property \webtoolsnz\scheduler\models\SchedulerLog[] $schedulerLogs
 */
class SchedulerTask extends \yii\db\ActiveRecord
{

    const TASK_FAILED = -1;
    const TASK_SUCCESS = 1;

    const STATUS_INACTIVE = 0;
    const STATUS_PENDING = 10;
    const STATUS_DUE = 20;
    const STATUS_RUNNING = 30;
    const STATUS_OVERDUE = 40;
    const STATUS_ERROR = 50;
    /**
     * @var array
     */
    private static $_statuses = [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_DUE => 'Due',
        self::STATUS_RUNNING => 'Running',
        self::STATUS_OVERDUE => 'Overdue',
        self::STATUS_ERROR => 'Error',
    ];

    // const SCENARIO_CREATE = 'create'; //default scenario
    const SCENARIO_SEARCH = 'search';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scheduler_task';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SEARCH] = $scenarios[self::SCENARIO_DEFAULT]; //copy all attributes from default)
        return $scenarios;
    }

    /**
     *
     */
    public static function label($n = 1)
    {
        return Yii::t('app', '{n, plural, =1{Scheduler Task} other{Scheduler Tasks}}', ['n' => $n]);
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
            [['name', 'cron'] , 'required', 'on'=>self::SCENARIO_DEFAULT],
            [['description', 'class_run','init_args'], 'string'],
            [['scheduler_task_id','active'], 'integer'],
            [['last_log_id', 'last_run'], 'safe'],
            [['name', 'cron'], 'string', 'max' => 45],
            [ 'active', 'default', 'value'=>1 , 'on'=>self::SCENARIO_DEFAULT],
            [ 'description', 'default', 'value'=>'', 'on'=>self::SCENARIO_DEFAULT]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scheduler_task_id' => Yii::t('app', 'scheduler_task_id'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'cron'=>Yii::t('app','cron expression'),
            'class_run'=>Yii::t('app','class to run'),
            'init_args' => Yii::t('app', 'Initial arguments'),
            'last_log_id'=>Yii::t('app','Last log'),
            'active' => Yii::t('app', 'Active'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedulerLogs()
    {
        return $this->hasMany( SchedulerLog::className(), ['scheduled_task_id' => 'scheduled_task_id']);
    }

    public function getLastLog()
    {
        return $this->hasOne( SchedulerLog::className(), ['scheduler_log_id' => 'last_log_id']);
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
            'sort' => ['defaultOrder'=>['scheduler_task_id'=>SORT_DESC]],
        ]);

        $this->load($params, $formName);

        $query->andFilterWhere([
            'scheduler_task_id' => $this->scheduler_task_id,
            'active' => $this->active,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'cron', $this->cron])
            ->andFilterWhere(['like', 'class_run', $this->class_run])
            ->andFilterWhere(['like', 'init_args', $this->init_args])
            ;

        return $dataProvider;
    }

    public function getId()
    {
        return $this->scheduler_task_id;
    }

    public function getNextRunDate($currentTime = 'now')
    {
        return CronExpression::factory($this->cron)
            ->getNextRunDate($currentTime)
            ;
    }

    public function getPreviousRunDate($currentTime = 'now')
    {
        return CronExpression::factory($this->cron)
            ->getPreviousRunDate($currentTime)
            ;
    }

    public function getStatus()
    {
        return self::STATUS_DUE;
    }

    public function getLockName()
    {
        return 'TaskLock_'.$this->id;
    }

    public function getInitArgs()
    {
        if ( !$this->init_args )
            return [];
        $decoded = json_decode( $this->init_args, true);
        //TODO check type
        return $decoded;
    }
}

