<?php
// use MyDomains\DomainFinder;
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Domain Checker</title>
</head>
<body>
    <div class="container-fluid">
        <form action="" method="GET" class="w-100">
            <input id="searchBar" class="w-100" type="text" name="domain" placeholder="Search domain name..."
                   value="<?php if (isset($_GET['domain'])) {
                       echo $_GET['domain'];
                   } ?>">
            <select name="tdl[]" multiple class="w-100" size="2" >
                <option value=".com">.com</option>
                <option value=".app">.app</option>
            </select>
            <button type="submit" class="w-100" >SEARCH</button>
        </form>
    </div>
    <?php
    //  error_reporting(0);
    if (isset($_GET['domain'])) {
        $domainFinder = new DomainFinder();
        $domainFinder->checkVariations($_GET['domain']);
        echo '<h3 style="margin-bottom: 0">AVAILABLE</h3>';
        echo $domainFinder->printAvails();
        echo '<h3 style="margin:100px 0 0 0">TAKEN</h3>';
        echo $domainFinder->printTaken();
    }
    ?>
</body>
</html>
