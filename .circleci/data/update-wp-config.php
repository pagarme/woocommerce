<?php

require_once("/app/wp-load.php");

$url = $_GET['url'];

update_option('siteurl', $url);
update_option('home', $url);

flush_rewrite_rules();

echo " - URL UPDATED!\n";
?>