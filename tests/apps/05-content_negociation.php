<?php

require_once dirname(__FILE__, 3) . '/lib/limo.php';

dispatch('/', 'content_negociation');

function content_negociation()
{
    //return var_dump($_SERVER['HTTP_ACCEPT']);
    if (http_ua_accepts('json')) {
        return "json";
    }

    if (http_ua_accepts('html')) {
        return "<h1>HTML</h1>";
    }

    return 'Oops';
}

run();