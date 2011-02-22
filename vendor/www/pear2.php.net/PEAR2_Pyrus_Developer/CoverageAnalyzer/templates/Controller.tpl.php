<html>
    <head>
        <title>Code Coverage Summary | PEAR2 Pyrus Developer</title>
        <link href="cover.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <?php if (isset($_SESSION['fullpath'])): ?>
        <h5>
        <a href="<?php echo $context->getRootLink(); ?>?restart">Current database: <?php echo $_SESSION['fullpath']; ?>.  Click to start over</a>
        </h5>
        <?php endif; ?>
        <?php echo $savant->render($context->actionable); ?>
    </body>
</html>