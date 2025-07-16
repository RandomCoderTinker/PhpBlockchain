<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

use Chain\Blockchain\Block;
use Chain\Utils\CryptoUnits;
use Chain\Wallet\WalletManager;
use Chain\Transaction\Transaction;

require dirname(__DIR__) . "/vendor/autoload.php";

// Initialize wallets
$wm = new WalletManager();
$alice = $wm->createWallet();
$bob = $wm->createWallet();
// For DPoS, pick a validator (could rotate among delegates)
$validator = $wm->createWallet();

echo "Alice Wallet:\n";
print_r($alice);
echo "\nBob Wallet:\n";
print_r($bob);
echo "\nValidator Wallet:\n";
print_r($validator);

// Fuzz config
$blockCount = rand(3, 15);
$maxTxPerBlock = 5;
$gasPrice = "0.00000001"; // 10 gwei
$previousHash = str_repeat('0', 64);

for ($b = 1; $b <= $blockCount; $b++) {
	echo "\n=== Block #{$b} ===\n";
	$txCount = rand(1, $maxTxPerBlock);
	$candidates = [];
	$nonce = 1;

	// Generate signed transactions (some will be tampered)
	for ($i = 0; $i < $txCount; $i++) {
		$from = ($i % 2 === 0) ? $alice : $bob;
		$to = ($i % 2 === 0) ? $bob : $alice;
		$amount = number_format(rand(1, 100) / 1000, 6, '.', '');
		$data = rand(0, 1) ? '0x' . bin2hex(random_bytes(rand(5, 20))) : NULL;

		$tx = new Transaction(
			$from['address'],
			$to['address'],
			$amount,
			$nonce++,
			$data,
			$gasPrice
		);
		$tx->sign($from['private_key']);

		// 10% chance to corrupt signature
		if (rand(1, 100) <= 10) {
			echo "-- Tampering TX #" . ($i + 1) . " to be invalid\n";
			$refProp = (new ReflectionClass($tx))->getProperty('signature');
			$refProp->setAccessible(TRUE);
			$refProp->setValue($tx, '0x' . bin2hex(random_bytes(65)));
		}
		$candidates[] = $tx;
	}

	// Filter out invalid transactions before building the block
	$validTxs = array_filter($candidates, fn(Transaction $t) => $t->isValid());
	$dropped = count($candidates) - count($validTxs);
	if ($dropped > 0) {
		echo "Dropped {$dropped} invalid TX(s) before block proposal\n";
	}

	// Build block with only valid transactions and sign by validator
	$block = new Block(
		$previousHash,
		$validTxs,
		$validator['address'],  // DPoS validator
		$validator['private_key']
	);

	// Display block summary
	echo "Previous Hash:  {$block->getPreviousHash()}\n";
	echo "Merkle Root:    {$block->getMerkleRoot()}\n";
	echo "Timestamp:      {$block->getTimestamp()}\n";
	echo "Nonce:          {$block->getNonce()}\n";
	echo "Block Hash:     {$block->getHash()}\n";
	echo "Validator:      {$block->getValidatorAddress()}\n";
	echo "Signature:      {$block->getValidatorSignature()}\n";
	echo "Total Gas Used: {$block->getTotalGasUsedUnits()} units\n";
	echo "Total Fees:     " . CryptoUnits::fromBaseUnit($block->getTotalFeesCollected()) . " CB\n";

	echo "Transactions in block (post-filter):\n";
	foreach ($block->getTransactions() as $idx => $tx) {
		$valid = $tx->isValid() ? 'valid' : 'INVALID';
		printf(
			"  [%d] %s -> %s | Amt: %s | Fee: %s | Gas: %s | Status: %s\n",
			$idx + 1,
			$tx->getFrom(),
			$tx->getTo(),
			CryptoUnits::fromBaseUnit($tx->getAmount()),
			CryptoUnits::fromBaseUnit($tx->getFee()),
			$tx->getGasLimit(),
			$valid
		);
	}

	// Validate and chain
	$validBlock = $block->isValid($previousHash);
	echo "Block valid? " . ($validBlock ? "Yes ✅" : "No ❌") . "\n";

	if ($validBlock) {
		$previousHash = $block->getHash();
	} else {
		echo "Skipping invalid block in chain\n";
	}
}