<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($error['type']) ?> | Ebcore Framework</title>
    <link rel="stylesheet" href="/_error/assets/css/error.css">
</head>
<body>
    <div class="error-container">
        <header class="error-header">
            <h1 class="error-title"><?= htmlspecialchars($error['type']) ?></h1>
            <div class="error-message"><?= htmlspecialchars($error['message']) ?></div>
        </header>

        <section class="error-section">
            <h2 class="error-section-title">Error Details</h2>
            <div class="error-details">
                <div class="code-snippet">
                    <code class="code-context">
                        <?php foreach ($error['context'] as $line => $code): ?>
                            <span <?= $line === $error['line'] ? 'class="error-line"' : '' ?>>
                                <span class="line-number"><?= $line ?></span>
                                <?= htmlspecialchars($code) ?>
                            </span><br>
                        <?php endforeach; ?>
                    </code>
                </div>
                <p>
                    <strong>File:</strong> <?= htmlspecialchars($error['file']) ?><br>
                    <strong>Line:</strong> <?= $error['line'] ?>
                </p>
            </div>
        </section>

        <section class="error-section">
            <h2 class="error-section-title">Stack Trace</h2>
            <div class="error-details">
                <pre class="stack-trace"><?= htmlspecialchars($error['trace']) ?></pre>
            </div>
        </section>

        <section class="error-section">
            <h2 class="error-section-title">Request Details</h2>
            <table class="error-table">
                <tr>
                    <th>URL</th>
                    <td><?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></td>
                </tr>
                <tr>
                    <th>Method</th>
                    <td><?= $_SERVER['REQUEST_METHOD'] ?></td>
                </tr>
                <tr>
                    <th>Client IP</th>
                    <td><?= $_SERVER['REMOTE_ADDR'] ?></td>
                </tr>
                <tr>
                    <th>User Agent</th>
                    <td><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT']) ?></td>
                </tr>
            </table>
        </section>

        <?php if (!empty($_POST)): ?>
        <section class="error-section">
            <h2 class="error-section-title">POST Data</h2>
            <div class="error-details">
                <pre><?= htmlspecialchars(print_r($_POST, true)) ?></pre>
            </div>
        </section>
        <?php endif; ?>

        <section class="error-section">
            <h2 class="error-section-title">Headers</h2>
            <table class="error-table">
                <?php foreach (getallheaders() as $name => $value): ?>
                <tr>
                    <th><?= htmlspecialchars($name) ?></th>
                    <td><?= htmlspecialchars($value) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <footer class="error-footer">
            <p>Ebcore Framework v<?= ebcore\Core\Config::get('app.version', '1.0.0') ?></p>
            <p>Environment: <?= ebcore\Core\Config::get('app.debug') ? 'Development' : 'Production' ?></p>
        </footer>
    </div>
</body>
</html> 