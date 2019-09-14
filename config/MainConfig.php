<?php
namespace App\Config;

class MainConfig
{
    // заголовок файлов, которые парсим
    const CSVFILECAPTION = 'date; A; B; C';
    // обрабатываем только файлы с маской
    const CSVFILEMASK = '*.csv';
    /** сюда будут выгружаться аггрегированные по дате данные
      * ВНИМАНИЕ! ВСЕ ФАЙЛЫ ОТСЮДА БУДУТ УДАЛЕНЫ ПРИ ЗАПУСКЕ ПРОГРАММЫ **/
    const OUTDIR = '/var/www/tests/click/data/AggregatedData/';
    // файл результата
    const RESULTFILE = '/var/www/tests/click/data/result.csv';
    // ну вот такая маска для строк с датами и А Б С, находит исправно, если уж совсем левые данные не вставлять типа 0-5.4..
    const REGEXP = '/\d{4}-\d{2}-\d{2};\s[.\d-]+;\s[.\d-];\s[.\d-]+$/';
    // разделить в csv файлах
    const DIVIDER = ';';
}