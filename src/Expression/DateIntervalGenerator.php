<?php

namespace Crazynds\IntervalExpression\Expression;

use Crazynds\IntervalExpression\Interval;
use Carbon\Carbon;
use DateTime;

class DateIntervalGenerator{

    private $startAt;
    private $interval;
    private $endAt;

    private $rules = [];
    private $rules_row = 0;
    private $rules_column = 0;

    private $iteration = 0;
    private $currentDate;
	private $nextDate;



    public function __construct(Interval $interval, Carbon $startAt, ?Carbon $endAt = null){
        $this->startAt = $startAt;
        $this->interval = $interval;
        $this->endAt = $endAt;
        $this->currentDate = $startAt->clone();
        $this->nextDate = null;
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
        if($this->rules_row!=0 && $rules_row == 0){
            $this->rules_row=count($this->rules)-1;
            $this->rules_column = count($this->rules[$this->rules_row]);
        }else{
            $this->rules_row = $rules_row;
            $this->rules_column = $rules_column;
        }
        dump($this);
    }


    public function endAt():?Carbon{
        return $this->endAt;
    }
    public function startAt():Carbon{
        return $this->startAt;
    }
    /**
     * Return current date of iterrator
     * if it is the first call will return the startAt even if it doesn't match the interval
     */
    public function current():?Carbon{
        return $this->currentDate;
    }
    public function iteration(){
        return $this->iteration;
    }

    /**
     * Check the next iterration, and if less than endAt return null
     * If endAt is null this functions always return the next iteration
     */
    public function next():?Carbon{
		if($this->nextDate){
			$this->currentDate = $this->nextDate;
			$this->nextDate = null;
		}else{
			$this->currentDate = $this->generateNextIteration($this->currentDate->clone());
			$this->iteration++;
			if($this->endAt?->lessThanOrEqualTo($this->currentDate)){
                return null;
            }
		}
        return $this->currentDate;
    }

    /**
     * Check the next iterration, and if less than endAt
     * If endAt is null this functions always return true
     */
    public function hasNext():bool{
        if($this->endAt==null)
            return true;
		if($this->nextDate)
            return true;

        $date = $this->generateNextIteration($this->currentDate->clone());
		$this->nextDate = $date;

        return $date->lessThanOrEqualTo($this->endAt);
    }

    /**
     * This function check if the date in parameter is in the date interval generator
     */
    public function match(?DateTime $date):bool{
        if(!$date)return false;
        $date = new Carbon($date);
        if($this->startAt->greaterThan($date))return false;
        if(!empty($this->endAt) && $this->endAt->lessThan($date))return false;

        switch($this->interval->getType()){
            case 'daily':
                $days=$this->startAt->diffInDays($date);
                return $days%$this->interval->getInterval() == 0;
            case 'monthly':
                $months=$this->startAt->diffInMonths($date);
                $rules_row=(int)($months/$this->interval->getInterval());
                $rules_row%=count($this->rules);
                return in_array($date->day-1,$this->rules[$rules_row]);
                break;
            case 'weekly':
                $weeks=$this->startAt->diffInWeeks($date);
                $rules_row=(int)($weeks/$this->interval->getInterval());
                $rules_row%=count($this->rules);
                return in_array($date->dayOfWeek,$this->rules[$rules_row]);
                break;
            case 'yearly':
                $years=$this->startAt->diffInYears($date);
                $rules_row=(int)($years/$this->interval->getInterval());
                $rules_row%=count($this->rules);
                return in_array($date->copy()->startOfYear()->diffInDays($date),$this->rules[$rules_row]);
                break;
        }

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
                        $newDate->startOfYear();
                        $newDate->addDays($increment);
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

