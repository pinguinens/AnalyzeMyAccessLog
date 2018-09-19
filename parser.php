<?php
$start = microtime(true); // Время начала выполнения скрипта

include 'metrics/memory.php';  // метрики
include 'classes/counter.php';  // Класс счётчиков

// Проверка аргументов команды
if (!isset($argv[1])) {
    die("Не указан файл лога\n");
} else {
    $filePath = $argv[1];
}
if (!file_exists($filePath)) {
    die("Указанный файл не существует\n");
}

// Получение списка поисковых роботов
$crawlerList = json_decode(file_get_contents('robots_list.txt'), true);

// Основное тело парсера
$stat = new CCounter($filePath, $crawlerList);
$stat->countAll();
$stats['views'] = $stat->getViews();
$stats['urls'] = $stat->getUrls();
$stats['traffic'] = $stat->getTraffic();
$stats['crawlers'] = $stat->getCrawlersStats();
$stats['statusCodes'] = $stat->getStatusCodesStats();

// Вывод результата работы
$statsJson = json_encode($stats);
echo $statsJson;
echo "\n";

// Техническая информация по работе скрипта
echo 'Файл: '. $argv[1];
echo "\n";

echo 'Время выполнения скрипта: '.(microtime(true) - $start).' сек.';
echo "\n";

echo 'Пиковое использование памяти PHP: '. formatBytes(memory_get_peak_usage());
echo "\n";
