# Interval Expression for PHP

[![Latest Stable Version](http://poser.pugx.org/crazynds/interval-expression/v)](https://packagist.org/packages/crazynds/interval-expression) 
[![Total Downloads](http://poser.pugx.org/crazynds/interval-expression/downloads)](https://packagist.org/packages/crazynds/interval-expression) 
[![License](http://poser.pugx.org/crazynds/interval-expression/license)](https://packagist.org/packages/crazynds/interval-expression) 

Interval system for schedules that represents recurrences that cannot be reached using cron.

## Installation

1.  Install the package

```shell
composer require crazynds/interval-expression
```

2. 

``` php

use Carbon\Carbon;
use Crazynds\IntervalExpression\Interval;

$dateStart = Carbon::parse('12-06-2022 14:30:00');
$dateEnd = Carbon::parse('01-08-2030 18:30:00');

$interval = new Interval();
// Expression for every 1 week on monday, wednesday and friday
$expression = '1 weekly 1,3,5';

$interval->parse($expression);
$generator = $interval->generator($dateStart,$dateEnd);

$dates = [];
$started = $generator->current()->format('d-m-Y H:i:s');
while($generator->hasNext()){
    $dates[] = $generator->next()->format('d-m-Y H:i:s');
}

var_dump($dates);

```

## Expression Definition

[0-9]+ (daily|weekly|monthly|yearly) {rules}*

- daily has no rules

- weekly rules:
   * {day of week [0-9]},{day of week [0-9]},...
   * 0,3,5 => (Sunday, Wednesday and Friday)
   * 1 => (Monday)
   * \* => (any 1 day of week)

- monthly rules:
   * {day of month [0-30]},{day of month [0-30]},...
   * 12,15 => (day 12 and 15 of every month iteration)
   * 10 => (day 10 of every month iteration)
   * \* => (any 1 day of month)

- yearly:
   * {day of year [0-365]},{day of year [0-365]},...
   * 12,15 => (day 12 and 15 of every year iteration)
   * 1 => (first day of every year iteration)
   * \* => (any 1 day of year)

There can be multiple rules, every iteration will cycle through them. Ex: if there are two rules, the third iteration will use the first rule.

## Examples

#### "2 weekly 1,5"
Description: Every 2 weeks on mondays and fridays
Inicial Date: 12-06-2022 14:30:00 
Output:
- "13-06-2022 14:30:00" (monday) 
- "17-06-2022 14:30:00" (friday)
- "27-06-2022 14:30:00" (monday)
- "01-07-2022 14:30:00" (friday)


#### "2 weekly 1 5"
Description: Every 2 weeks, the first occurence will be on a monday an the second will be on a friday.
Inicial Date: 12-06-2022 14:30:00 
Output:
- "13-06-2022 14:30:00" (monday) 
- "01-07-2022 14:30:00" (friday)
- "11-07-2022 14:30:00" (monday) 
- "29-07-2022 14:30:00" (friday)


#### "2 weekly 1,5 5"
Description: Every 2 weeks, the first occurence will happen both on a monday and a friday, and the second will be only on a friday.
Inicial Date: 12-06-2022 14:30:00 
Output:
- "13-06-2022 14:30:00" (monday)
- "17-06-2022 14:30:00" (friday)
- "01-07-2022 14:30:00" (friday)
- "11-07-2022 14:30:00" (monday) 

#### "1 monthly 0,14 19"
Description: Every month, on first occurence will happen on the 1st and 15th day of the month, and the second will be only on a 20th day of the month.
Inicial Date: 12-06-2022 14:30:00 
Output:
- "15-06-2022 14:30:00" (15th day)
- "20-07-2022 14:30:00" (20th day)
- "01-08-2022 14:30:00" (1st day)
- "15-08-2022 14:30:00" (15th day)


## Tested and supported versions of PHP

- 8.X
- 7.X
