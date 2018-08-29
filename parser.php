<?php
$start = microtime(true); // Время начала выполнения скрипта

include 'metrics/misc.php';  // метрики
include 'classes/counter.php';  // Класс счётчиков

// Основное тело парсера
if (!isset($argv[1])) {
    die("Не указан файл лога\n");
} else {
    $filePath = $argv[1];
}
if (!file_exists($filePath)) {
    die("Указанный файл не существует\n");
}

$stat = new CCounter($filePath);
$stats['views'] = $stat->countViews();
$stats['urls'] = $stat->countUrls();
$stats['traffic'] = $stat->countTraffic();
$stats['crawlers'] = $stat->getCrawlers(
    array(
        'Google' => array('google'),
        'Yandex' => array('yandex', 'YandexBot'),
        'Bing' => array('bing', 'bingbot'),
        'Baidu' => array('baidu', 'Baiduspider')
        )
);
$stats['statusCodes'] = $stat->statusCodes();

// Вывод результата работы
$statsJson = json_encode($stats);
echo $statsJson;
echo "\n";

// Техническая информация по работе скрипта
echo 'Время выполнения скрипта: '.(microtime(true) - $start).' сек.';
echo "\n";

echo 'Пиковое потребление памяти PHP: '. formatBytes(memory_get_peak_usage());
echo "\n";
