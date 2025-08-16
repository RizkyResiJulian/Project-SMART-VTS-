<?php
$log_file = 'chat_log.txt';

if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        echo "<div>" . htmlspecialchars($line) . "</div>";
    }
} else {
    echo "<div>Belum ada percakapan.</div>";
}
?>
