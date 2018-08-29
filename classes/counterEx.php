<?php

// Класс счётчиков
class CCounter
{
    private $filePath;

    // Чтение файла
    private function readFile()
    {
        $handle = fopen($this->filePath, "r");

        while (!feof($handle)) {
            yield trim(fgets($handle));
        }

        fclose($handle);
    }

    public function __construct($file)
    {
        $this->filePath = $file;
    }

    // Подсчитывает хиты
    public function countViews($input)
    {
        $input = $this->readFile();
        $views = 0;
        
        foreach ($input as $iteration) {
            $views++;
        }
        
        return $views;
    }

    // Подсчитывает уникальные URL
    public function countUrls()
    {
        $input = $this->readFile();

        $urls = 0;
        $buffer = array();
        
        foreach ($input as $iteration) {
            $bufferCur = array();

            preg_match("/[[:upper:]]\s(.*)\sH/", $iteration, $bufferCur);
            
            if (isset($bufferCur[1]) and (!in_array($bufferCur[1], $buffer))) {
                $buffer[] = $bufferCur[1];
                $urls++;
            } elseif (!isset($bufferCur[1])) {
                preg_match("/\"(.*)\"\s\d/", $iteration, $bufferCur);
                
                if ($iteration !== "") {
                    $buffer[] = $bufferCur[1];
                    $urls++;
                }
            } else {
                $buffer[] = $bufferCur[1];
            }
        }
        
        return $urls;
    }

    // Подсчитывает обьем траффика
    public function countTraffic()
    {
        $input = $this->readFile();

        $buffer = 0;

        foreach ($input as $iteration) {
            preg_match("/\s(\d*)\s(\d*)\s/", $iteration, $bufferCur);

            if (isset($bufferCur[1]) and ($bufferCur[1] == 200)) {
                $buffer += $bufferCur[2];
            }
        }

        return $buffer;
    }

    // Определяет поисковых ботов
    public function getCrawlers($crawlersList)
    {
        $input = $this->readFile();

        $buffer = array();

        foreach ($input as $iteration) {
            foreach ($crawlersList as $provider => $bot) {
                $bots = implode('|', $bot);

                preg_match("/".$bots."[a-zA-Z]*\d*/i", $iteration, $bufferCur);

                if (array_key_exists($provider, $buffer)) {
                    $buffer[$provider] += count($bufferCur);
                } else {
                    $buffer += [$provider => count($bufferCur)];
                }
            }
        }

        return $buffer;
    }

    // Определяет коды ответов
    public function statusCodes()
    {
        $input = $this->readFile();
        
        $bufferArr = array();
        
        foreach ($input as $iteration) {
            preg_match("/\"\s(\d*)\s/", $iteration, $bufferCur);

            if (isset($bufferCur[1])) {
                $code = $bufferCur[1];
            }
            if (array_key_exists($code, $bufferArr)) {
                $bufferArr[$code]++;
            } else {
                $bufferArr += [$code => 1];
            }
        }

        return $bufferArr;
    }

    
    public function getCounters($crawlersList)
    {
        $this->inputData = $this->readFile();

        $counters['views'] = $this->countViews();
        $counters['urls'] = $this->countUrls();
        $counters['traffic'] = $this->countTraffic();
        $counters['crawlers'] = $this->getCrawlers($crawlersList);
        $counters['statusCodes'] = $this->statusCodes();

        return($counters);
    }
}
