<?php

function formatDate() {
    $date = getdate();

    $formatDate = strval($date['year']);
    $formatDate .= '-' . strval($date['mon']);
    $formatDate .= '-' . strval($date['mday']);
    $formatDate .= '.' . strval($date['hours']);
    $formatDate .= ':' . strval($date['minutes']);

    return $formatDate;
}
