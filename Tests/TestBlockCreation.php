<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

use Chain\Blockchain\Block;
use Chain\Blockchain\Blockchain;

require dirname(__DIR__) . "/vendor/autoload.php";

echo "⛓️  Creating new blockchain...\n";

$blockchain = new Blockchain();
echo "➕ Adding blocks...\n";

$blockchain->addBlock(new Block(0, [['from' => '0xAlice', 'to' => '0xBob', 'amount' => 10]], "0"));
$blockchain->addBlock(new Block(1, [['from' => '0xBob', 'to' => '0xCarol', 'amount' => 25]], $blockchain->getBlock(0)->getHash()));
$blockchain->addBlock(new Block(2, [['from' => '0xCarol', 'to' => '0xDave', 'amount' => 5]], $blockchain->getBlock(1)->getHash()));

echo "\n� Block Summary:\n";
foreach ($blockchain->getAllBlocks() as $block) {
	echo "Block #{$block->index}\n";
	echo "  Hash         : {$block->hash}\n";
	echo "  Prev Hash    : {$block->previousHash}\n";
	echo "  TX Count     : " . count($block->transactions) . "\n";
	echo "  Nonce        : {$block->nonce}\n";
	echo "  Timestamp    : " . date('Y-m-d H:i:s', $block->timestamp) . "\n\n";
}

echo "✅ Chain Valid? " . ($blockchain->isValid() ? "Yes" : "No") . "\n";
