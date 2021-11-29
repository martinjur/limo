<?php

require_once dirname(__FILE__, 3) . '/lib/limo.php';

dispatch('/', 'test');
function test()
{
    return render(session_name());
}

run();
