<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Doctrine-Skeleton</title>
    <link type="text/css" rel="stylesheet" href="css/stylesheet.css" />
</head>

<body>
    <header>
        <h1>Doctrine-Skeleton</h1>
    </header>

    <?php require 'navi.tpl.php'; ?>
    <?php require 'flash_message.tpl.php'; ?>
    <?php require 'errors.tpl.php'; ?>

    <section>
        <?php require $template; ?>
    </section>
</body>
</html>