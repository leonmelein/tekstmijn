<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/favicon.ico">

    <title><?=$this->e($title)?></title>
    <!-- Bootstrap core CSS -->
    <link href="/vendor/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/vendor/bootstrap/dist/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="/vendor/application/application.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat|Roboto" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="signin">
    <div class="container signin-container">


        <form class="form-signin" method="post" action="/login/">

            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <img src="/assets/img/logo.svg" alt="Tekstmijn" class="img-rounded img-responsive" height="350" width="350"><br><br>
                </div>
            </div>
            <div class="row">
                <?php
                if($_GET["logged_out"] == "true") {
                    echo '<div class="alert alert-success alert-dismissable" role="alert">
                           <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                           <strong>Uitgelogd.</strong> Je bent uitgelogd.
                      </div>';
                } else if ($_GET["failed"] == "true") {
                    echo '<div class="alert alert-danger alert-dismissable" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                        <b>Inloggen mislukt.</b> Probeer het opnieuw of vraag je docent.
                      </div>';
                } else if ($_GET["registration"] == "true") {
                    echo '<div class="alert alert-success alert-dismissable" role="alert">
                           <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
                           <strong>Gelukt.</strong> Je bent geregistreerd.
                      </div>';
                }
                ?>
            </div>
            <label for="username" class="sr-only">Gebruikersnaam</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Leerlingnummer" aria-label="Leerlingnummer" required autofocus>
            <label for="password" class="sr-only">Wachtwoord</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Wachtwoord" aria-label="Wachtwoord" required>
            <button class="btn btn-lg btn-primary btn-block" type="submit" aria-label="Inloggen">Inloggen</button>
            <a class="btn btn-lg btn-primary btn-block" href="/register/" aria-label="Registreren">Registreren</a>
        </form>

    </div> <!-- /container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/vendor/jquery/dist/jquery.min.js"></script>
    <script src="/vendor/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>

