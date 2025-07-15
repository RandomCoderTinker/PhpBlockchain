<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

use Chain\Utils\Logger;
use Chain\Config\Config;

require dirname(__DIR__) . "/vendor/autoload.php";

echo "📝 Logger Output Test\n============================\n";

// Instantiate once for clarity
$logger = Logger::getInstance();

// Define a test context
$context = [
	'user_id' => 42,
	'module' => 'LoggerTest',
	'env' => 'dev',
];

// Emit logs at all levels
echo "🔧 Writing test logs...\n";
$logger->error("This is a test error log", $context);
$logger->warning("This is a test warning log", $context);
$logger->info("This is a test info log", $context);
$logger->debug("This is a test debug log", $context);
$logger->success("This is a test success log", $context);

// Output suggestion for log consumers
echo "\n✅ Log entries written. Check your logger output destination (console, file, etc.).\n";

// Optional: Display file if logs go to a file (if your logger supports it)
$logFile = Config::getInstance()->getLogFile(); // e.g. "./logs/app.log"
if (file_exists($logFile)) {
	echo "\n📂 Recent Log Output (tail -10):\n----------------------------\n";
	$lines = explode("\n", trim(file_get_contents($logFile)));
	$tail = array_slice($lines, -10);
	foreach ($tail as $line) {
		echo $line . "\n";
	}
} else {
	echo "\n⚠️  No log file found at expected path: $logFile\n";
}
