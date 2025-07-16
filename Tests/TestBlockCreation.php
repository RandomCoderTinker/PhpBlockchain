<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

use Chain\Utils\Logger;
use Chain\Blockchain\Block;
use Chain\Blockchain\Blockchain;

require dirname(__DIR__) . "/vendor/autoload.php";

// Test the blockchain
echo "⛓️  Creating new blockchain...\n";

// 1) Init Blockchain
$blockchain = new Blockchain();
echo "➕ Adding blocks...\n";

// 2) Add sample blocks
$blockchain->addBlock(new block(0, [['from' => '0xAlice', 'to' => '0xBob', 'amount' => 10]], "0"));
$blockchain->addBlock(new block(1, [['from' => '0xBob', 'to' => '0xCarol', 'amount' => 25]], "1"));
$blockchain->addBlock(new block(2, [['from' => '0xCarol', 'to' => '0xDave', 'amount' => 5]], "2"));

// 3) Print blocks
echo "\n📦 Block Summary: \n";
foreach ($blockchain->getAllBlocks() as $block) {
	echo "Block #{$block->index}\n";
	echo "  Hash         : {$block->hash}\n";
	echo "  Prev Hash    : {$block->previousHash}\n";
	echo "  TX Count     : " . count($block->transactions) . "\n";
	echo "  Nonce        : {$block->nonce}\n";
	echo "  Timestamp    : " . date('Y-m-d H:i:s', $block->timestamp) . "\n\n";
}
