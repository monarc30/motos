<?php
/**
 * Página de erro estilo Laravel: mensagem, arquivo/linha, trecho de código e stack trace.
 * Uso: definir $errorTitle, $errorMessage, $errorFile, $errorLine, $errorTrace (array) e incluir este arquivo.
 */
if (!isset($errorTitle)) $errorTitle = 'Erro';
if (!isset($errorMessage)) $errorMessage = '';
if (!isset($errorFile)) $errorFile = '';
if (!isset($errorLine)) $errorLine = 0;
if (!isset($errorTrace)) $errorTrace = [];

$snippetLines = 12;
$half = (int) floor($snippetLines / 2);
$startLine = max(1, $errorLine - $half);
$endLine = $errorLine + $half;
$codeSnippet = [];
if ($errorFile && is_readable($errorFile)) {
    $lines = file($errorFile, FILE_IGNORE_NEW_LINES);
    if ($lines !== false) {
        $total = count($lines);
        $endLine = min($total, $endLine);
        $startLine = min($startLine, max(1, $endLine - $snippetLines + 1));
        for ($i = $startLine - 1; $i < $endLine && $i < $total; $i++) {
            $codeSnippet[$i + 1] = $lines[$i];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($errorTitle) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
            background: #1e1e1e;
            color: #e0e0e0;
            line-height: 1.5;
            padding: 2rem;
            font-size: 14px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ff6b6b;
            margin-bottom: 0.5rem;
            word-break: break-word;
        }
        .location {
            color: #888;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .location a { color: #6eb3f7; text-decoration: none; }
        .location a:hover { text-decoration: underline; }
        .block {
            background: #252526;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: 1px solid #333;
        }
        .block-title {
            background: #2d2d30;
            padding: 0.5rem 1rem;
            font-weight: 600;
            color: #cccccc;
            font-size: 0.85rem;
        }
        .code {
            padding: 0;
            overflow-x: auto;
        }
        .code-line {
            display: flex;
            min-height: 1.5em;
            padding: 0 1rem;
        }
        .code-line.num {
            border-left: 3px solid transparent;
        }
        .code-line.error {
            background: rgba(255, 107, 107, 0.15);
            border-left: 3px solid #ff6b6b;
        }
        .code-num {
            width: 3rem;
            flex-shrink: 0;
            text-align: right;
            padding-right: 1rem;
            color: #6e7681;
            user-select: none;
        }
        .code-line.error .code-num { color: #ff6b6b; }
        .code-content { flex: 1; white-space: pre-wrap; word-break: break-all; }
        .trace-list { list-style: none; }
        .trace-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #333;
        }
        .trace-item:last-child { border-bottom: none; }
        .trace-item:hover { background: #2a2a2a; }
        .trace-file {
            color: #6eb3f7;
            font-size: 0.9rem;
        }
        .trace-call {
            color: #dcdcaa;
            margin-top: 0.25rem;
            font-size: 0.85rem;
        }
        .trace-line { color: #6e7681; font-size: 0.8rem; margin-top: 0.15rem; }
        .footer { margin-top: 2rem; color: #6e7681; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($errorMessage ?: $errorTitle) ?></h1>
        <?php if ($errorFile): ?>
        <p class="location">
            em <strong><?= htmlspecialchars($errorFile) ?></strong> na linha <strong><?= (int) $errorLine ?></strong>
        </p>
        <?php endif; ?>

        <?php if (!empty($codeSnippet)): ?>
        <div class="block">
            <div class="block-title">Trecho do código</div>
            <div class="code">
                <?php foreach ($codeSnippet as $num => $content): ?>
                <div class="code-line num <?= ($num === (int)$errorLine) ? 'error' : '' ?>">
                    <span class="code-num"><?= $num ?></span>
                    <span class="code-content"><?= htmlspecialchars($content) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($errorTrace)): ?>
        <div class="block">
            <div class="block-title">Stack trace</div>
            <ul class="trace-list">
                <?php foreach ($errorTrace as $i => $frame):
                    $file = $frame['file'] ?? '';
                    $line = $frame['line'] ?? 0;
                    $func = $frame['function'] ?? '';
                    $class = $frame['class'] ?? '';
                    $type = $frame['type'] ?? '';
                    $call = $class ? $class . $type . $func : $func;
                ?>
                <li class="trace-item">
                    <div class="trace-file">#<?= $i ?> <?= htmlspecialchars($file) ?></div>
                    <div class="trace-call"><?= htmlspecialchars($call) ?>()</div>
                    <?php if ($line): ?><div class="trace-line">linha <?= (int) $line ?></div><?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <p class="footer">Erro registrado em storage/logs/php_errors.log</p>
    </div>
</body>
</html>
