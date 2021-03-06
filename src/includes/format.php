<?php

function printArrayTable($arrayTable)
{
    list($rows, $headers) = getHeadersAndRows($arrayTable);
    $totalLength = 0;

    echo "\n  ";
    foreach ($headers as $headerText=>$headerInfo) {
        headerFormat(" " .str_pad($headerText, $headerInfo['headerLength']+3)."\033[0m");
        $totalLength = $totalLength + $headerInfo['headerLength'] + 3;
    }

    echo "\n  ";
    infoFormat(str_pad(" ", $totalLength + count($headers)));

    foreach ($rows as $row) {
        echo "\n  ";
        foreach ($row as $header => $value) {
            infoFormat(" " . str_pad($value, $headers[$header]['headerLength']+3));
        }
    }

    echo "\n  ";
    infoFormat(str_pad(" ", $totalLength + count($headers)));

    echo "\n\n";
}

function headerFormat($txt)
{
    echo "\033[0;30m\033[47m".$txt."\033[0m";
}

function whiteFormat($txt)
{
    return "\033[0;30m\033[47m".$txt."\033[0m";
}

function greenFormat($txt)
{
    return "\033[0;30m\033[42m".$txt."\033[0m";
}

function redFormat($txt)
{
    return "\033[0;30m\033[41m".$txt."\033[0m";
}

function infoFormat($txt)
{
    echo "\033[40m".$txt."\033[0m";
}

function getHeadersAndRows($sqlResult)
{
    $result = array();

    foreach ($sqlResult as $key=>$row) {
        foreach ($row as $field=>$value) {
            $result[$key][$field] = preg_replace('/[^A-Za-z0-9\. -_]/', '', $value);
        }
    }

    $headers = array();

    if (empty($result)) {
        return array($result, $headers);
    }

    foreach ($row as $header=>$value) {
        $headers[$header] = array('headerLength' => max(array_map('mb_strlen', array_column($result, $header))));
    }

    foreach ($headers as $headerText=>$header) {
        $headers[$headerText]['headerLength'] = max($headers[$headerText]['headerLength'], mb_strlen($headerText));
    }

    return array($result, $headers);

}

function squaredText($text, $indented=2)
{
    if (strlen($text) > 70) {
        $text = substr($text, 0, 66) . '...';
    }

    $length  = strlen($text);
    $topDown = '+--' . str_repeat('-', $length) . '--+';
    $middle  = '|  ' . $text . '  |';

    $indentation = str_repeat("\t", $indented);

    echo $indentation . $topDown . "\n" . $indentation . $middle . "\n" . $indentation . $topDown . "\n\n";
}