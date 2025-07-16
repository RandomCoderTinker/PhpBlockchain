<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

use Chain\Utils\Hex;
use Chain\Wallet\WalletManager;
use Chain\Transaction\Transaction;

require dirname(__DIR__) . "/vendor/autoload.php";

/**
 * Run fuzz tests for random transactions.
 *
 * @param int $iterations How many random transactions to generate
 * @return void
 */
function fuzzTestTransactions(int $iterations = 50): void
{
	$walletManager = new WalletManager();
	$success = 0;
	$failure = 0;

	for ($i = 1; $i <= $iterations; $i++) {
		$sender = $walletManager->createWallet();
		$recipient = $walletManager->createWallet();

		// <-- Fixed random decimal generation -->
		$amount = sprintf('%.2f', rand(1, 10000) / 100);        // up to 100.00 RTN
		$gasPrice = sprintf('%.8f', rand(1, 1000000) / 100000000); // up to 0.01 RTN
		$nonce = rand(0, 10);
		$dataLen = rand(0, 128);
		$rawData = ['payload' => bin2hex(random_bytes($dataLen))];
		$data = Hex::bin2hex($rawData);

		try {
			$tx = new Transaction(
				$sender['address'],
				$recipient['address'],
				$amount,
				$nonce,
				$data,
				$gasPrice
			);
			$signed = $tx->sign($sender['private_key']);

			if ($tx->isValid()) {
				$success++;
			} else {
				$failure++;
				echo "[{$i}] ❌ Verification failed for TX: {$signed}\n";
			}
		} catch (\Throwable $e) {
			$failure++;
			echo "[{$i}] ⚠️ Exception: " . $e->getMessage() . "\n";
		}
	}

	echo "\nFuzz test complete: {$iterations} iterations\n";
	echo "  ✅ Successes: {$success}\n";
	echo "  ❌ Failures:  {$failure}\n";
	echo "Success rate: " . round($success / $iterations * 100, 2) . "%\n";
}

// Run a quick smoke test
echo "=== Single Test ===\n";
$walletManager = new WalletManager();
$senderWallet = $walletManager->createWallet();
$recipientWallet = $walletManager->createWallet();

$amount = "0.01";
$gasPrice = "0.00000001";
$nonce = 1;
$data = Hex::bin2hex(['string' => 'Test payload']);

$tx = new Transaction(
	$senderWallet['address'],
	$recipientWallet['address'],
	$amount,
	$nonce,
	$data,
	$gasPrice
);

$signed = $tx->sign($senderWallet['private_key']);
echo "Signed TX: $signed\n";
echo "Valid? " . ($tx->isValid() ? "Yes ✅" : "No ❌") . "\n";
// Dump full TX as array
echo "\nTransaction Array:\n";
print_r($tx->toArray());

echo "\n\n=== Fuzz Testing ===\n";
fuzzTestTransactions(10);
