<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use Chain\Blockchain\Block;
use Chain\Blockchain\Blockchain;
use Chain\Transaction\Transaction;
use Chain\Wallet\WalletManager;

echo "⛓️  Creating new blockchain...\n";

$walletManager = new WalletManager();
$wallets = [];

// 1. Generate 10 wallets
echo "� Generating wallets...\n";
for ($i = 0; $i < 10; $i++) {
	$wallets[] = $walletManager->createWallet();
}

$blockchain = new Blockchain();

// 2. Generate 3 blocks with random transactions
echo "➕ Generating blocks with random signed transactions...\n";

$prevHash = "0";

for ($blockIndex = 0; $blockIndex < 3; $blockIndex++) {
	$txs = [];

	// Create 5 transactions per block
	for ($i = 0; $i < 5; $i++) {
		$senderIndex = random_int(0, count($wallets) - 1);
		$recipientIndex = random_int(0, count($wallets) - 1);
		while ($recipientIndex === $senderIndex) {
			$recipientIndex = random_int(0, count($wallets) - 1);
		}

		$sender = $wallets[$senderIndex];
		$recipient = $wallets[$recipientIndex];

		$tx = new Transaction(
			$sender['address'],
			$recipient['address'],
			(string)random_int(1, 100),
			random_int(1, 1000)
		);
		$tx->sign($sender['private_key']);

		// Validate before adding
		if (!$tx->verify($sender['public_key'])) {
			echo "❌ Transaction signature failed verification.\n";
			continue;
		}

		$txs[] = $tx->toArray();
	}

	$block = new Block($blockIndex, $txs, $prevHash);
	$blockchain->addBlock($block);
	$prevHash = $block->getHash();
}

// 3. Print block summaries
echo "\n� Block Summary:\n";
foreach ($blockchain->getAllBlocks() as $block) {
	echo "Block #{$block->index}\n";
	echo "  Hash         : {$block->hash}\n";
	echo "  Prev Hash    : {$block->previousHash}\n";
	echo "  TX Count     : " . count($block->transactions) . "\n";
	echo "  Nonce        : {$block->nonce}\n";
	echo "  Timestamp    : " . date('Y-m-d H:i:s', $block->timestamp) . "\n\n";
}

// 4. Final validation
echo "✅ Chain Valid? " . ($blockchain->isValid() ? "Yes" : "No") . "\n";