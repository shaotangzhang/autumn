<?php

use Autumn\Database\Db;
use Autumn\System\Route;

$error ??= null;
if (!$error instanceof Throwable) {
    return;
}

$errorMessage = $error->getMessage();
$errorFile = $error->getFile();
$errorLine = $error->getLine();
$errorTrace = $error->getTraceAsString();
$trace = $error->getTrace()[0] ?? null;

// 读取错误所在文件的代码
$fileLines = file($errorFile);

// 确定要显示的行范围
$startLine = max(1, $errorLine - 9);
$endLine = min(count($fileLines), $errorLine + 9);


$histories = Db::histories(); // 假设 Db::histories() 方法可以获取 PDO 执行记录
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-container {
            margin-top: 50px;
        }

        .error-container pre {
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
<div class="container error-container mb-5">
    <h2>An error occurred</h2>
    <div class="alert alert-danger" role="alert">
        <strong>Error:</strong> <?php echo $errorMessage; ?><br>
        <strong>File:</strong> <?php echo $errorFile; ?><br>
        <strong>Line:</strong> <?php echo $errorLine; ?><br>
        <strong>Trace:</strong><br>
    </div>

    <?php

    $shown = false;
    // 获取第一个 trace 中的文件路径和行号
    if ($firstTrace = $error->getTrace()[0] ?? null) {
        $traceFile = $firstTrace['file'] ?? '';
        if (file_exists($traceFile)) {
            $traceLine = $firstTrace['line'];
            $shown = true;

            // 读取 trace 文件的代码
            $traceFileLines = file($traceFile);

            // 确定要显示的行范围
            $traceStartLine = max(1, $traceLine - 9);
            $traceEndLine = min(count($traceFileLines), $traceLine + 9);
            ?>

            <div class="border rounded mt-3 bg-light p-3">
                <h6><?php echo $traceFile; ?></h6>
                <?php
                // 显示 trace 文件代码
                for ($i = $traceStartLine; $i <= $traceEndLine; $i++) {
                    $lineNumber = str_pad($i, strlen($traceEndLine), ' ', STR_PAD_LEFT); // 获取行号，并在左侧填充空格，以保证对齐
                    $line = $traceFileLines[$i - 1];
                    if ($i === $traceLine) {
                        // 高亮显示 trace 错误行
                        echo '<div class="bg-warning">';
                        echo '<span class="text-muted">' . $lineNumber . ' | </span>';
                        highlight_string($line);
                        echo '</div>';
                    } else {
                        echo '<span class="text-muted">' . $lineNumber . ' | </span>';
                        highlight_string($line);
                    }
                }
                ?>
            </div>
        <?php }
    }


    if (!$shown) {
        ?>
        <div class="border rounded mt-3 bg-light p-3">
            <h6><?php echo $errorFile; ?></h6>
            <?php
            // 显示代码
            for ($i = $startLine; $i <= $endLine; $i++) {
                $lineNumber = str_pad($i, strlen($endLine), ' ', STR_PAD_LEFT); // 获取行号，并在左侧填充空格，以保证对齐
                $line = $fileLines[$i - 1];
                if ($i === $errorLine) {
                    // 高亮显示错误行
                    echo '<div class="bg-warning">';
                    echo '<span class="text-muted">' . $lineNumber . ' | </span>';
                    highlight_string($line);
                    echo '</div>';
                } else {
                    echo '<span class="text-muted">' . $lineNumber . ' | </span>';
                    highlight_string($line);
                }
            }
            ?>
        </div>
    <?php } ?>

    <pre class="border rounded mt-3 bg-light p-3"><?php echo $errorTrace; ?></pre>

    <!-- 显示 PDO 执行记录 -->
    <?php if (!empty($histories)) { ?>
        <div class="mt-3">
            <h2>Database History</h2>
            <?php foreach ($histories as $history) : ?>
                <div class="card rounded-0 border-0 mb-3">
                    <div class="card-header">
                        <code><?php echo $history['sql'] ?? $history[0] ?? null; ?></code>
                    </div>
                    <?php if (empty($history['params'] ?? $history[1] ?? null)) {
                        continue;
                    } ?>
                    <table class="table table-sm table-striped">
                        <tr>
                            <th>Name</th>
                            <th>Value</th>
                        </tr>
                        <?php foreach ($history['params'] ?? $history[1] ?? [] as $name => $value) : ?>
                            <tr>
                                <td><?php echo $name; ?></td>
                                <td>
                                    <pre><?php print_r($value); ?></pre>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    <?php } ?>

    <table class="table table-sm table-striped">
        <tr>
            <th>Route</th>
            <th>Callback</th>
        </tr>

        <?php foreach (Route::all() as $method => $routes) : ?>
            <tr>
                <th colspan="2"><?php echo $method; ?></th>
            </tr>
            <?php foreach ($routes as $route) { ?>
                <tr>
                    <td><?php echo $route->getPath(); ?></td>
                    <td>
                        <pre><?php print_r($route->getCallback()); ?></pre>
                    </td>
                </tr>
            <?php } ?>
        <?php endforeach; ?>
    </table>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>