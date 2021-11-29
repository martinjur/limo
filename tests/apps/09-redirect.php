<?php

require_once dirname(__FILE__, 3) . '/lib/limo.php';

dispatch('/', 'index');
function index()
{
    $pairs = explode('&', $_SERVER['QUERY_STRING']);
    $params = [];
    foreach ($pairs as $pair) {
        $keyAndValue = explode('=', $pair);
        $params[$keyAndValue[0]] = count($keyAndValue) > 1 ? $keyAndValue[1] : '';
    }
    array_shift($params);

    redirect_to('/redirected', $params);
}

dispatch('/redirected', 'redirected');
function redirected()
{
    print $_SERVER['QUERY_STRING'];
}

run();
