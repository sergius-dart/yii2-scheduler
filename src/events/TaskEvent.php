<?php


namespace webtoolsnz\scheduler\events;

use yii\base\Event;


class TaskEvent extends Event
{
    public $task;
    public $task_obj;
    public $exception = null;
    public $success = true;

    public $cancel = false;
}