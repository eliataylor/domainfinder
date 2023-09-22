<?php
// use MyDomains\DomainFinder;
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});
if (!isset($_GET['domain'])) {
    echo 'what domain?';
    die();
}
$test = $_GET['domain'];
$domainFinder = new DomainFinder();
$domainFinder->checkVariations($test);
header('Content-Type: application/json');
echo $domainFinder->toJson();
