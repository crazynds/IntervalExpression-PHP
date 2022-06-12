<?php

namespace Crazynds\IntervalExpression;

use Carbon\Carbon;
use Crazynds\IntervalExpression\Expression\DateIntervalGenerator;

class Interval {
    private $intervalType;
    private $interval;
    private $rules;

    public function __construct()
    {
        $this->intervalType = 'daily';
        $this->interval = 1;
        $this->rules = [
            "*"
        ];
    }

    /**
     * A expressão vai ser definida da seguinte forma
     *
     * [0-9]+ (daily|weekly|monthly|yearly) {rules}+
     *
     * daily has no rules
     *
     * weekly rules:
     *  {day of week [0-9]},{day of week [0-9]},...
     *  0,3,5 => (Sunday, Wednesday and Friday)
     *  1 => (Monday)
     *  * => (any 1 day of week)
     *
     * monthly rules:
     *  {day of month [0-30]},{day of month [0-30]},...
     *  12,15 => (day 12 and 15 of every month iteration)
     *  10 => (day 10 of every month iteration)
     *  * => (any 1 day of month)
     *
     * yearly:
     *  {day of year [0-365]},{day of year [0-365]},...
     *  12,15 => (day 12 and 15 of every year iteration)
     *  1 => (first day of every year iteration)
     *  * => (any 1 day of year)
     *
     * You can have N rules, for every iteration they will use the Xº rule,
     *  if doesnt exist they will back to the first rule and repeat the process
     *
     */
    public function parse(string $expression){
        $expression = trim($expression);
        if(!$this->validate($expression))return false;

        $list = explode(' ',$expression);
        $this->interval = intval(array_shift($list));
        $this->intervalType = strtolower(array_shift($list));
        $this->rules = $list;
        return $this;
    }

    public function validate(string $expression){
        $expression = trim($expression);
        $regexExpression = "/^[0-9]* (daily|weekly ((([0-6],)*[0-6]\s|\*\s)*)(((([0-6],)*)?[0-6]|\*))|montly (((([0-2]?[0-9]|30),)*([0-2]?[0-9]|30)\s|\*\s)*)((((([0-2]?[0-9]|30),)*)?([0-2]?[0-9]|30)|\*))|yearly (((([0-2]?[0-9]?[0-9]|3[0-5][0-9]|36[0-5]),)*([0-2]?[0-9]?[0-9]|3[0-5][0-9]|36[0-5])\s|\*\s)*)((((([0-2]?[0-9]?[0-9]|3[0-5][0-9]|36[0-5]),)*)?([0-2]?[0-9]?[0-9]|3[0-5][0-9]|36[0-5])|\*)))$/i";
        if(preg_match($regexExpression,$expression,$match)){
            return true;
        }else{
            return false;
        }
    }

    public function generator(?Carbon $startAt= null,?Carbon $endAt = null){
        if(!$startAt) $startAt = Carbon::now();
        return new DateIntervalGenerator($this,$startAt,$endAt);
    }


    public function getType(){
        return $this->intervalType;
    }

    public function getInterval(){
        return $this->interval;
    }

    public function getRules(){
        return $this->rules;
    }


}
