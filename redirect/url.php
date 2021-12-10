<?php
function RecupUrl(){
return(base64_decode($_SERVER['QUERY_STRING']));

}

header('HTTP/1.0 301 Moved Permanently');
header('Location: ' .RecupUrl());

echo RecupUrl();

?>