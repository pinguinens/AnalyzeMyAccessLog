<?php
$start = microtime(true); // Время начала выполнения скрипта

include 'metrics/memory.php';  // метрики
include 'classes/counterEx.php';  // Класс счётчиков

// Проверка аргументов команды
if (!isset($argv[1])) {
    die("Не указан файл лога\n");
} else {
    $filePath = $argv[1];
}
if (!file_exists($filePath)) {
    die("Указанный файл не существует\n");
}

// Основное тело парсера
$stat = new CCounterEx($filePath, array(
    'Google' => array('google'),
    'Yandex' => array('yandex', 'YandexBot'),
    'Bing' => array('bing', 'bingbot'),
    'Baidu' => array('baidu', 'Baiduspider')
    )
);
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
echo 'Время выполнения скрипта: '.(microtime(true) - $start).' сек.';
echo "\n";

echo 'Пиковое использование памяти PHP: '. formatBytes(memory_get_peak_usage());
echo "\n";
