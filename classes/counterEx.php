<?php

// Класс счётчиков
class CCounterEx
{
    private $filePath;
    private $crawlerList;
    private $stats = array();

    // Чтение файла
    private function readFile()
    {
        $handle = fopen($this->filePath, "r");

        while (!feof($handle)) {
            yield trim(fgets($handle));
        }

        fclose($handle);
    }

    // Подсчитывает хиты
    private function countViews($string)
    {
        if (isset($string) and ($string !== "")) {
            $view = 1;
        } else {
            $view = 0;
        }

        if (key_exists('views', $this->stats)) {
            $this->stats['views'] += $view;
        } else {
            $this->stats += ['views' => $view];
        }
        
        return $this->stats['views'];
    }

    // Подсчитывает уникальные URL
    private function countUrls($string)
    {
        preg_match("/[[:upper:]]\s(.*)\sH/", $string, $bufferCur);

        if (isset($bufferCur[1]) and !key_exists('urls', $this->stats)) {
            $this->stats += ['urls' => array($bufferCur[1])];
            return 1;
        } elseif (!isset($bufferCur[1]) and !key_exists('urls', $this->stats)) {
            preg_match("/\"(.*)\"\s\d/", $iteration, $bufferCur);
            
            if (($string !== "") and isset($bufferCur[1])) {
                $this->stats += ['urls' => array($bufferCur[1])];
                return 1;
            }
        }
            
        if (isset($bufferCur[1]) and (!in_array($bufferCur[1], $this->stats['urls']))) {
            $this->stats['urls'][] = $bufferCur[1];
        } elseif (!isset($bufferCur[1])) {
            preg_match("/\"(.*)\"\s\d/", $string, $bufferCur);
            
            if (($string !== "") and isset($bufferCur[1])) {
                $this->stats['urls'][] = $bufferCur[1];
            }
        }

        return count($this->stats['urls']);
    }

    // Подсчитывает обьем траффика
    private function countTraffic($string)
    {
        preg_match("/\s(\d*)\s(\d*)\s/", $string, $bufferCur);

        if (isset($bufferCur[2]) and !key_exists('traffic', $this->stats)) {
            if (isset($bufferCur[1]) and ($bufferCur[1] == 200)) {
                $this->stats += ['traffic' => $bufferCur[2]];
                return $this->stats['traffic'];
            }
        } else {
            if (isset($bufferCur[1]) and ($bufferCur[1] == 200)) {
                $this->stats['traffic'] += $bufferCur[2];
            }
        }

        return $this->stats['traffic'];
    }

    // Определяет поисковых ботов
    private function crawlersStats($string)
    {
        if (!key_exists('crawlers', $this->stats)) {
            $this->stats += ['crawlers' => array()];
        }

        foreach ($this->crawlerList as $provider => $bot) {
            $bots = implode('|', $bot);

            preg_match("/".$bots."[a-zA-Z]*\d*/i", $string, $bufferCur);

            if (array_key_exists($provider, $this->stats['crawlers'])) {
                $this->stats['crawlers'][$provider] += count($bufferCur);
            } else {
                $this->stats['crawlers'] += [$provider => count($bufferCur)];
            }
        }

        return $this->stats['crawlers'];
    }

    // Определяет коды ответов
    private function statusCodesStats($string)
    {
        if (!key_exists('statusCodes', $this->stats)) {
            $this->stats += ['statusCodes' => array()];
        }
        
        preg_match("/\"\s(\d*)\s/", $string, $bufferCur);

        if (($string !== "") and isset($bufferCur[1])) {
            $code = $bufferCur[1];
            
            if (array_key_exists($code, $this->stats['statusCodes'])) {
                $this->stats['statusCodes'][$code]++;
            } else {
                $this->stats['statusCodes'] += [$code => 1];
            }
        }

        return $this->stats['statusCodes'];
    }

    public function __construct($file, $crawlerList)
    {
        $this->filePath = $file;
        $this->crawlerList = $crawlerList;
    }

    // Подсчитывает все показатели
    public function countAll() {
        $input = $this->readFile();

        foreach ($input as $string) {
            // Хиты
            $this -> countViews($string);

            // Уникальные URL
            $this->countUrls($string);

            // Обьем траффика
            $this->countTraffic($string);

            // Определяет поисковых ботов
            $this->crawlersStats($string);
            
            // Определяет коды ответов
            $this->statusCodesStats($string);
        }
    }

    // Выводит количство хитов
    public function getViews() {
        return $this->stats['views'];
    }
    
    // Выводит количство уникальных URL
    public function getUrls() {
        return count($this->stats['urls']);
    }
    
    // Выводит количство уникальных URL
    public function getTraffic() {
        return $this->stats['traffic'];
    }
    
    // Выводит статистику поисковых ботов
    public function getCrawlersStats() {
        return $this->stats['crawlers'];
    }
    
    // Выводит статистику кодов ответа
    public function getStatusCodesStats() {
        return $this->stats['statusCodes'];
    }
}
