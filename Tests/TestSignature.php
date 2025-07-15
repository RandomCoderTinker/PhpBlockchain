<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use Chain\Cryptography\Signature;
use Chain\Wallet\WalletManager;

// === INIT ===
$startTime = microtime(TRUE);
echo "🔐 Signature Test Suite\n========================\n";

// === Create Wallet ===
echo "\n🔧 Generating test wallet...\n";
$walletManager = new WalletManager();
try {
	$wallet = $walletManager->createWallet();
} catch (Exception $e) {
	die("❌ Unable to create wallet: {$e->getMessage()}\n");
}
print_r($wallet);

// === Test Messages ===
$message = "This is a test message that we signing and verifying";
$messageTampered = "This is a test messаge that we signing and verifying"; // with Cyrillic 'а'

// === Signing ===
echo "\n✍️  Signing message...\n";
$signature = Signature::sign($message, $wallet['private_key']);
print_r($signature);

// === Verification: Valid ===
echo "\n🔍 Verifying original message...\n";
$valid = Signature::verify($message, $signature['signature'], $wallet['public_key']);
echo "✔️  Signature valid? " . ($valid ? "Yep ✅" : "Nay ❌") . "\n";

// === Verification: Tampered ===
echo "\n🚨 Verifying tampered message (Cyrillic attack)...\n";
$valid = Signature::verify($messageTampered, $signature['signature'], $wallet['public_key']);
echo "✔️  Signature valid? " . ($valid ? "Yep ✅" : "Nay ❌") . "\n";

// === Recovery ===
echo "\n🔁 Recovering signer address from signature...\n";
$recovered = Signature::recoverAddress($message, $signature['signature']);
echo "🔍 Recovered address: {$recovered}\n";
echo "🔗 Matches wallet? " . ($recovered === $wallet['address'] ? "Yes ✅" : "No ❌") . "\n";

// === Fuzz Testing ===
echo "\n🧪 Running fuzzy verification tests...\n";
$fuzzFailures = 0;
$fuzzTotal = 50;

for ($i = 0; $i < $fuzzTotal; $i++) {
	$mutation = mutate($message);
	$fuzzed = $mutation['mutated'];
	if (!$mutation['changed']) {
		echo "⚠️  Fuzz case $i skipped: mutation had no effect\n";
		continue;
	}

	$isValid = Signature::verify($fuzzed, $signature['signature'], $wallet['public_key']);
	if ($isValid) {
		echo "❌ Fuzz fail (case $i):\n   → Message: $fuzzed\n";
		$fuzzFailures++;
	}
}

echo "\n📃 Fuzz test result: " . ($fuzzTotal - $fuzzFailures) . " passed ✅, $fuzzFailures failed ❌";
echo "\n⏱️  Done in " . round(microtime(TRUE) - $startTime, 4) . "s\n";

// === Helpers ===

/**
 * Applies a random mutation to the input string for fuzz testing.
 */
function mutate(string $input): array
{
	$mutated = $input;

	switch (random_int(0, 4)) {
		case 0:
			// Replace 'a' with Cyrillic 'а'
			$mutated = str_replace('a', 'а', $mutated);
			break;
		case 1:
			// Bit-flip in random char
			$pos = random_int(0, strlen($mutated) - 1);
			$mutated[$pos] = chr(ord($mutated[$pos]) ^ (1 << random_int(0, 7)));
			break;
		case 2:
			// Remove char
			$pos = random_int(0, strlen($mutated) - 1);
			$mutated = substr_replace($mutated, '', $pos, 1);
			break;
		case 3:
			// Add random ASCII char
			$pos = random_int(0, strlen($mutated));
			$mutated = substr_replace($mutated, chr(random_int(33, 126)), $pos, 0);
			break;
		case 4:
			// Swap adjacent chars
			if (strlen($mutated) > 2) {
				$pos = random_int(0, strlen($mutated) - 2);
				$tmp = $mutated[$pos];
				$mutated[$pos] = $mutated[$pos + 1];
				$mutated[$pos + 1] = $tmp;
			}
			break;
	}

	return [
		'mutated' => $mutated,
		'changed' => $mutated !== $input,
	];
}
