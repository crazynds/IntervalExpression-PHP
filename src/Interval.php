<?php

use Crazynds\IntervalExpression\Expression;

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
     *  if doesnt exist they will back to first and repeat the process
     *
     */
    public function parse(string $expression){

    }

}
