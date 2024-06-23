<?php

if(env('DEBUG')) {
    include(__DIR__ . '/debug.php');
    return;
}

$error ??= null;
if (!$error instanceof Throwable) {
    return;
}

$code = $error->getCode();
if ($file = realpath(__DIR__ . '/' . $code . '.php')) {
    return include $file;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="alert alert-danger" role="alert">
            An error occurred: <?php echo $error->getMessage(); ?>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>