<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

use kornrunner\Keccak;
use Chain\Cryptography\MerkleTree;

require dirname(__DIR__) . "/vendor/autoload.php";

echo "\n🌴 Merkle Tree Test\n----------------------\n";

$transactions = [
	'tx1: Alice -> Bob (5)',
	'tx2: Bob -> Carol (3)',
	'tx3: Dave -> Alice (2)',
	'tx4: Eve -> Bob (7)',
	'tx5: Bob -> Alice (0.1)',
];

$tree = new MerkleTree($transactions);
$root = $tree->getRoot();

echo "🫚 Merkle Root: {$root}\n\n";

foreach ($transactions as $index => $tx) {
	$leafHash = Keccak::hash('LEAF' . $tx, 256);
	$proof = $tree->getProof($index);
	$valid = MerkleTree::verifyProof($leafHash, $proof, $root);

	echo "TX #$index: \"$tx\"\n";
	echo "  ➤ Leaf: {$leafHash}\n";
	echo "  ➤ Proof: " . json_encode($proof) . "\n";
	echo $valid
		? "  ✅ Verified\n\n"
		: "  ❌ Verification FAILED\n\n";
}

echo $tree::selfTest() ? "✅ MerkleTree::selfTest() passed\n" : "❌ MerkleTree::selfTest() failed\n";