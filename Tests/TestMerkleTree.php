<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use Chain\Cryptography\MerkleTree;

echo "\n🌲 Merkle Tree Test Suite\n=============================\n";

// Sample transaction list
$transactions = [
	'tx1: Alice -> Bob (5)',
	'tx2: Bob -> Carol (3)',
	'tx3: Dave -> Alice (2)',
	'tx4: Eve -> Bob (7)',
	'tx5: Bob -> Alice (0.1)',
];

echo "🔧 Building Merkle Tree from " . count($transactions) . " transactions...\n";

$tree = new MerkleTree($transactions);
$root = $tree->getRoot();

echo "🔗 Merkle Root: $root\n\n";

// Verification loop
$allPassed = TRUE;

foreach ($transactions as $index => $tx) {
	echo "🔍 Verifying TX #$index: \"$tx\"\n";

	$leafHash = MerkleTree::hashLeaf($tx);
	$proof = $tree->getProof($index);

	$valid = MerkleTree::verifyProof($leafHash, $proof, $root);

	echo "  ➤ Leaf Hash : $leafHash\n";
	echo "  ➤ Proof     : " . json_encode($proof, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
	echo $valid ? "  ✅ Valid Proof\n\n" : "  ❌ Invalid Proof\n\n";

	if (!$valid) {
		$allPassed = FALSE;
	}
}

// Run internal MerkleTree self-test (structure, symmetry, etc.)
echo "🔁 Self-Test Check...\n";
$passedSelfTest = MerkleTree::selfTest();

echo $passedSelfTest ? "✅ MerkleTree::selfTest() passed\n" : "❌ MerkleTree::selfTest() failed\n";

if ($allPassed && $passedSelfTest) {
	echo "\n🏁 All Merkle tests passed!\n";
} else {
	echo "\n🚨 One or more Merkle proofs failed. Check log for diagnostics.\n";
}
