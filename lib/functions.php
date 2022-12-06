<?php

function formatDate() {
    $date = getdate();

    $formatDate = strval($date['year']);

    if ($date['mon'] < 10 ) {
        $formatDate .= '-' . '0' . strval($date['mon']);
    } else {
        $formatDate .= '-' . strval($date['mon']);
    }
    
    $formatDate .= '-' . strval($date['mday']);
    $formatDate .= '.' . strval($date['hours']);
    $formatDate .= ':' . strval($date['minutes']);

    return $formatDate;
}
