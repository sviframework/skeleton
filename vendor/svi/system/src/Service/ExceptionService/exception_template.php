<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Exception</title>
</head>
<?php
$path = function ($path) use ($root) {
    return str_replace($root . '/', '', $path);
};
$printR = function ($array) {
    $result = [];
    foreach ($array as $key => $val) {
        $key = is_numeric($key) ? '' : $key . ': ';
        if (is_object($val)) {
            $result[] = $key . get_class($val);
        } elseif (is_array($val)) {
            $result[] = $key . 'Array(args)';
        } elseif ($val === false) {
            $result[] = 'false';
        } elseif ($val === NULL) {
            $result[] = 'null';
        } else {
            $result[] = $key . $val;
        }
    }

    return implode(', ', $result);
}
?>
<?php /** @var Throwable $e */ ?>
<body style="background: #ccc; padding: 20px;">
<div style="max-width: 1240px; margin: 0 auto">
    <h1 style="margin-top: 10px; padding: 0 15px;">Ooops! Looks like something went wrong</h1>
    <div style="background: #eee; border-radius: 6px; border: #999 1px solid; padding: 12px 15px;">
        <h2 style="margin-top: 0"><?=get_class($e)?>: <?=$e->getMessage()?></h2>
        <p>In <b><?=$path($e->getFile())?></b> at line <b><?=$e->getLine()?></b></p>
        <h3>Call stack:</h3>
        <ol>
            <?php $i = 0 ?>
            <?php foreach ($e->getTrace() as $trace): ?>
                <?php $i++ ?>
                <li><p>
                        <?php if (isset($trace['file'])): ?>In <b><?=$path($trace['file'])?></b> at line <b><?=$trace['line']?></b><br/><?php endif; ?>
                        <?php
                        $args = [];
                        foreach ($trace['args'] as $arg) {
                            if (is_object($arg)) {
                                $args[] = get_class($arg);
                            } elseif (is_array($arg)) {
                                $args[] = 'Array(' . $printR($arg) . ')';
                            } elseif ($arg === false) {
                                $args[] = 'false';
                            } elseif ($arg === NULL) {
                                $args[] = 'null';
                            } else {
                                $args[] = '' . $arg;
                            }
                        }
                        $args = implode(', ', $args);
                        ?>
                        <?php if (isset($trace['class'])): ?><?=$trace['class']?>::<?php endif ?><?=$trace['function']?>(<?=$args?>)
                    </p></li>
            <?php endforeach ?>
        </ol>
        <?php if (false): ?><pre style="margin: 0"><?php print_r($e) ?></pre><?php endif ?>
    </div>
</div>
</body>
</html>