<?php
declare(strict_types = 1);

$MonthNames=["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
$weekDaysNames=["Пн","Вт","Ср","Чт","Пт","Сб","Вс"];

// Согласно блока "Дополнительно" из ДЗ разрешаем ввод параметров при запуске
// в формате: exercise-02_dop.php [год_начала_расчета] [месяц_начала_расчета] [количество_расчитываемых_месяцев] [день_первый_рабочий]
if ($argc>1){
    for($i=1;$i<count($argv);$i++){
        if(!is_numeric($argv[$i]) || intval($argv[$i]) <= 0){
            die("Все параметры должны быть положительными числами".PHP_EOL."По порядку: год месяц сколько_месяцев_смотреть первый_день_выхода".PHP_EOL.PHP_EOL);
        }
    }
}
$year = isset($argv[1])?intval($argv[1]) : intval(date('Y'));
$month = isset($argv[2])?intval($argv[2]) : intval(date('m'));
$monthsToCount = isset($argv[3])?intval($argv[3]) : 1;
$firstDay = isset($argv[4])?intval($argv[4]) : 1;
// Объявляем первый день трудоустройства

$weekend = [6,7]; // Обязательные выходные (номера дней недели)
$graph = [1,1,0,0]; // График масив рабочего цикла, где 1 - рабочий день, 0 - выходной

show_months_calendar($year,$month, $monthsToCount,$firstDay);

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
function formatDayOutput(int $day,int $workday=0):string{
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
 * @param  mixed $year год
 * @param  mixed $month месяц
 * @param  mixed $monthsToCount сколько месяцев считать
 * @param  mixed $firstDay день выхода в смену
 * @return void
 */
function show_months_calendar(int $year,int $month, int $monthsToCount=1,int $firstDay = 1):void{
    // не смог придумать, как не используя globl использовать эти переменные
    global $MonthNames, $weekDaysNames, $weekend, $graph;
    $j=0;

    for($m=0;$m<$monthsToCount;$m++){
    // Берем первое число месяца от начала расчетов
        $firstDayDate = DateTimeImmutable::createFromFormat('Y-m-d',"$year-$month-$firstDay");
    // Берем сначала первое число месяца, а потом сдвигаем на нужное количество месяцев 
    // (если не сдвинуть день, то в случае выхода в конце месяца вероятны скачки через один месяц)
        $currentMonth = $firstDayDate->modify("first day of this month +$m month"); 
        $currentMonthInt = intval($currentMonth->format('m'));
        echo "График работы".PHP_EOL."на {$MonthNames[$currentMonthInt - 1]} " .$currentMonth->format('Y').PHP_EOL;
        $monthDays = cal_days_in_month(CAL_GREGORIAN, $currentMonthInt, intval($currentMonth->format('Y')));
        echo $monthDays.PHP_EOL;
        // Выводим список дней недели (для красоты и наглядности)
        for($weekD=0;$weekD<7;$weekD++){
            echo $weekDaysNames[$weekD]."\t";
        }
        echo PHP_EOL;
        // Проходим по всему месяцу
        for($i=1;$i<=$monthDays;$i++){
            if($j===count($graph))
                $j=0;
            $graphState=$graph[$j];
            $isWorkDay = 0;
            $currentDay = DateTime::createFromFormat('Y-m-d',"$year-$currentMonthInt-$i");
            $weekday = $currentDay->format('N');
            // Если ткущая в цикле дата меньше даты выхода - пропускать и выводить без оформления
            if($firstDayDate <= $currentDay){   
                if($graphState===1){
                    if(!in_array($weekday,$weekend)){
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
            if($i===1 && $weekday>1){
                for($k=1;$k<$weekday;$k++)
                    echo "\t";
            }
            // выводим дату
            echo formatDayOutput($i,$isWorkDay) . (($weekday < 7) ? "\t" : PHP_EOL);
        }
        echo PHP_EOL.PHP_EOL;    
    }
}
