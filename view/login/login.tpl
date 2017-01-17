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

        <form class="form-signin">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <img src="/assets/img/logo.svg" alt="Hofstad" class="img-rounded img-responsive"><br><br>
                </div>
            </div>
            <div class="row"></div>
            <label for="inputEmail" class="sr-only">Emailadres</label>
            <input type="email" id="inputEmail" class="form-control" placeholder="Emailadres" required autofocus>
            <label for="inputPassword" class="sr-only">Wachtwoord</label>
            <input type="password" id="inputPassword" class="form-control" placeholder="Wachtwoord" required>
            <div class="checkbox">
                <label>
                    <input type="checkbox" value="remember-me"> Onthouden
                </label>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Inloggen</button>
        </form>

    </div> <!-- /container -->
</body>
</html>

