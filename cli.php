<?php
// use MyDomains\DomainFinder;
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$domainFinder = new DomainFinder();
$domainFinder->checkVariations('abc');
// $domainFinder->checkNouns();
echo '----------------------------AVAILABLE----------------------------' . PHP_EOL;
echo $domainFinder->printAvails(true);
echo '----------------------------TAKEN----------------------------' . PHP_EOL;
echo $domainFinder->printTaken(true);
