<!--
  ~ Copyright (c) 2019. DW Web-Engineering
  ~ https://www.teamspeak-interface.de
  ~ Developer: Daniel W.
  ~
  ~ License Informations: This program may only be used in conjunction with a valid license.
  ~ To purchase a valid license please visit the website www.teamspeak-interface.de
  -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <title>404 | Tea(m)speak Interface</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/main.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/responsive.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/icons.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/error.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="assets/css/fontawesome/font-awesome.min.css">
    <!--[if IE 7]>
    <link rel="stylesheet" href="assets/css/fontawesome/font-awesome-ie7.min.css">
    <![endif]-->
    <!--[if IE 8]>
    <link href="assets/css/ie8.css" rel="stylesheet" type="text/css"/>
    <![endif]-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
</head>

<body class="error">

<div class="title">
    <h2 class="text-danger">Whoops! There was an error.</h2>
</div>

<div class="actions">
    <div class="list-group">
        <li class="list-group-item list-group-header align-center">
            For more details, please enable the debug mode, repeat the action you want, check the log files, and contact
            the support team if necessary.
        </li>
        <?php if (isset($response)): ?>
            <li class="list-group-item">
                <?= $response; ?>
            </li>
        <?php endif; ?>
        <a href="https://teamspeak-interface.net" class="list-group-item" target="_blank">
            <i class="icon-question"></i> Go to the Tea(m)speak Interface Support Forum  <i class="icon-angle-right align-right"></i>
        </a>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    TSI - Tea(m)speak Interface<br>&copy; 2019 by DW <\> Web-Engineering
</div>
<script type="text/javascript" src="assets/js/libs/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="assets/js/libs/lodash.compat.min.js"></script>
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="assets/js/libs/html5shiv.js"></script>
<![endif]-->
</body>
</html>