<?php

use Autumn\Database\Db;
use Autumn\Lang\HTML;
use Autumn\System\Route;

$histories = Db::histories();
?>
<p style="height: 2rem;"></p>
<div class="card" style="position: fixed; bottom: 0; max-height: 40vh; width: 100%">
    <div class="card-header">
        <a href="#debugPanel" data-bs-toggle="collapse" class="d-block">Debug</a>
    </div>
    <div class="card-body collapse h-100 overflow-auto" id="debugPanel">
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
                        <td><?php echo HTML::encode($route->getPattern()); ?></td>
                        <td>
                            <pre><?php print_r($route->getCallback()); ?></pre>
                        </td>
                    </tr>
                <?php } ?>
            <?php endforeach; ?>
        </table>

        <table class="table table-sm table-striped">
            <tr>
                <th>Session Key</th>
                <th>Session Value</th>
            </tr>

            <?php foreach ($_SESSION ?? [] as $name => $value) : ?>
                <tr>
                    <td><?php echo HTML::encode($name); ?></td>
                    <td>
                        <pre><?php print_r($value); ?></pre>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>