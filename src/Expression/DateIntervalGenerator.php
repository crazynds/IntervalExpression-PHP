<?php

namespace Crazynds\IntervalExpression\Expression;

use Crazynds\IntervalExpression\Interval;
use Carbon\Carbon;

class DateIntervalGenerator{

    private $startAt;
    private $interval;
    private $endAt;

    private $rules = [];
    private $rules_row = 0;
    private $rules_column = 0;

    private $iteration = 0;
    private $currentDate;



    public function __construct(Interval $interval, Carbon $startAt, ?Carbon $endAt = null){
        $this->startAt = $startAt;
        $this->interval = $interval;
        $this->endAt = $endAt;
        $this->currentDate = $startAt->clone();
        $rules = $interval->getRules();
        foreach($rules as $rule){
            $array = array_unique(explode(',',$rule));
            sort($array);
            if(count($array)>0)
                $this->rules[] = $array;
        }
        $this->syncPointersDate();
    }

    private function syncPointersDate(){
        $auxDate = $this->startAt->clone();
        do{
            $rules_row = $this->rules_row;
            $rules_column = $this->rules_column;

            $auxDate = $this->generateNextIteration($auxDate);
        }while($auxDate->lessThanOrEqualTo($this->startAt()));
        $this->rules_row = $rules_row;
        $this->rules_column = $rules_column;
    }


    public function endAt(){
        return $this->endAt;
    }
    public function startAt(){
        return $this->startAt;
    }
    public function iteration(){
        return $this->iteration;
    }
    public function current(){
        return $this->currentDate;
    }
    public function next(){
        $this->currentDate = $this->generateNextIteration($this->currentDate);
        $this->iteration++;
        if($this->endAt->lessThanOrEqualTo($this->currentDate))return null;
        return $this->currentDate;
    }
    public function hasNext(){
        $rules_row = $this->rules_row;
        $rules_column = $this->rules_column;

        $date = $this->generateNextIteration($this->currentDate->clone());

        $this->rules_row = $rules_row;
        $this->rules_column = $rules_column;

        return $date->lessThanOrEqualTo($this->endAt);
    }

    private function generateNextIteration(Carbon $date):Carbon{
        if($this->rules_row >= count($this->rules)){
            $this->rules_row = 0;
            return $this->generateNextIteration($date);
        }
        if($this->rules_column >= count($this->rules[$this->rules_row])){
            $this->rules_row++;
            $this->rules_column=0;
            switch($this->interval->getType()){
                case 'daily':
                    //$date->addDays($this->interval->getInterval());
                    break;
                case 'monthly':
                    $date = $date->addMonth($this->interval->getInterval());
                    break;
                case 'weekly':
                    $date = $date->addWeeks($this->interval->getInterval());
                    break;
                case 'yearly':
                    $date = $date->addYears($this->interval->getInterval());
                    break;
            }
            return $this->generateNextIteration($date);
        }
        $rule = $this->rules[$this->rules_row][$this->rules_column++];
        $newDate = $date->clone();
        switch($this->interval->getType()){
            case 'daily':
                $newDate->addDays($this->interval->getInterval());
                break;
            case 'monthly':
                if($rule!='*'){
                    $increment = intval($rule);
                    do{
                        $month = $newDate->month;
                        $newDate->startOfMonth()->addDays($increment);
                    }while($month<$newDate->month && $increment<=30);
                }
                break;
            case 'weekly':
                if($rule!='*'){
                    $days = [
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday'
                    ];
                    $newDate->startOfWeek(Carbon::SUNDAY)->subDay();
                    $newDate->next($days[intval($rule)]);
                }
                break;
            case 'yearly':
                if($rule!='*'){
                    $increment = intval($rule);
                    do{
                        $year = $newDate->year;
                        $newDate->startOfYear()->addDays($increment);
                    }while($year<$newDate->year && $increment<=365);
                }
                break;
        }
        $days = $newDate->endOfDay()->diffInDays($date);
        if($newDate->lessThan($date)){
            $date->subDays($days+1);
        }else{
            $date->addDays($days);
        }
        return $date;
    }


}

