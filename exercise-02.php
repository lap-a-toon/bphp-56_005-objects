<?php
declare(strict_types = 1);

$MonthNames=["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
$weekDaysNames=["Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"];


$year = 2024;
$month = 2;
$monthsToCount = 3;
$firstDay = 1;
// Объявляем первый день трудоустройства

$weekend = [6, 7]; // Обязательные выходные (номера дней недели)
$graph = [1, 1, 0, 0]; // График масив рабочего цикла, где 1 - рабочий день, 0 - выходной

show_months_calendar($year, $month, $monthsToCount, $firstDay);

/**
 * formatDayOutput
 * Оформление дней в цвета соответствующие рабочим и выходным
 * 0 - еще не определено (не устроен) - без оформления
 * 1 - Выходные - зелёным
 * 2 - Рабочие - красным
 *
 * @param  int $day Номер дня в месяце
 * @param  int $workday Признак рабочий ли это день
 * @return string
 */
function formatDayOutput(int $day, int $workday=0) : string
{
    switch ($workday){
        case 2:
            $output = "\033[31m{$day}\033[0m";
            break;
        case 1:
            $output = "\033[32m{$day}\033[0m";
            break;
        default:
            $output = "{$day}";
    }
    return $output;
}

/**
 * show_months_calendar
 *
 * @param  int $year год
 * @param  int $month месяц
 * @param  int $monthsToCount сколько месяцев считать
 * @param  int $firstDay день выхода в смену
 * @return void
 */
function show_months_calendar(int $year, int $month, int $monthsToCount=1, int $firstDay = 1) : void
{
    // не смог придумать, как не используя globl использовать эти переменные
    global $MonthNames, $weekDaysNames, $weekend, $graph;
    $j = 0;

    for($m = 0; $m<$monthsToCount; $m++){
    // Берем первое число месяца от начала расчетов
        $firstDayDate = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$firstDay");
    // Берем сначала первое число месяца, а потом сдвигаем на нужное количество месяцев 
    // (если не сдвинуть день, то в случае выхода в конце месяца вероятны скачки через один месяц)
        $currentMonth = $firstDayDate->modify("first day of this month +$m month"); 
        $currentMonthInt = (int) $currentMonth->format('m');
        echo "График работы".PHP_EOL."на {$MonthNames[$currentMonthInt - 1]} " . $currentMonth->format('Y') . PHP_EOL;
        $monthDays = cal_days_in_month(CAL_GREGORIAN, $currentMonthInt, (int) $currentMonth->format('Y'));
        echo $monthDays.PHP_EOL;
        // Выводим список дней недели (для красоты и наглядности)
        for($weekD=0; $weekD<7; $weekD++){
            echo $weekDaysNames[$weekD] . "\t";
        }
        echo PHP_EOL;
        // Проходим по всему месяцу
        for($i = 1; $i <= $monthDays; $i++){
            if($j === count($graph))
                $j = 0;
            $graphState=$graph[$j];
            $isWorkDay = 0;
            $currentDay = DateTime::createFromFormat('Y-m-d', "$year-$currentMonthInt-$i");
            $weekday = $currentDay->format('N');
            // Если ткущая в цикле дата меньше даты выхода - пропускать и выводить без оформления
            if($firstDayDate <= $currentDay){   
                if($graphState === 1){
                    if(!in_array($weekday, $weekend)){
                        $isWorkDay = 2;
                        $j++;
                    }else{
                        $isWorkDay = 1;
                    }
                }else{
                    $isWorkDay = 1;
                    $j++;
                }
            }
            // для красоты выводит табуляции, чтобы дни начала месяца соответствовали дням недели
            if($i === 1 && $weekday > 1){
                for($k = 1; $k < $weekday; $k++)
                    echo "\t";
            }
            // выводим дату
            echo formatDayOutput($i, $isWorkDay) . (($weekday < 7) ? "\t" : PHP_EOL);
        }
        echo PHP_EOL.PHP_EOL;    
    }
}
