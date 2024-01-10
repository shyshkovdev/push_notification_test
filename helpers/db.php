<?php
function getConnection(): PDO
{
    $connection = new \App\Factories\DatabaseFactory();
    return $connection->init();
}


if (!function_exists('dd')) {
    function dd()
    {
        echo '<pre>';
        array_map(function($x) {var_dump($x);}, func_get_args());
        die;
    }
}