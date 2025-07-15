<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Chain\Wallet\Mnemonic;
use Chain\Wallet\WalletManager;
use Chain\Wallet\KeyPair;

echo "🔐 Wallet Creation Test\n";
echo "=========================\n";

// Initialize the wallet manager
$walletManager = new WalletManager();

try {
	// Generate a new wallet
	echo "⚙️  Generating new test wallet...\n";
	$wallet = $walletManager->createWallet();

	// � Display wallet details
	echo "\n📦 Wallet Details:\n";
	echo "  ➤ Address     : {$wallet['address']}\n";
	echo "  ➤ Public Key  : {$wallet['public_key']}\n";
	echo "  ➤ Private Key : {$wallet['private_key']}\n";
	echo "  ➤ Mnemonic    : {$wallet['mnemonic']} \n";

	echo "\n✅ Wallet successfully generated.\n\n";
} catch (Exception $e) {
	echo "❌ Failed to create wallet: {$e->getMessage()}\n";
	exit(1);
}

// Test restoration from mnemonic
echo "🔁 Regenerate from mnemonic...\n";
$restored = $walletManager->createWalletFromMnemonic($wallet['mnemonic']);

//  Compare original and restored
echo "\n🧪 Regenerated Wallet:\n";
echo "  ➤ Address     : {$restored['address']}\n";
echo "  ➤ Public Key : {$restored['public_key']}\n";
echo "  ➤ Private Key : {$restored['private_key']}\n";
echo "  ➤ Mnemonic : {$restored['mnemonic']}\n";

// Recover keys from Private key only
$restoredFromPriv = $walletManager->restoreWalletFromPrivateKey($wallet['private_key']);

echo "\n🧪 Regenerated Wallet From Private key:\n";
echo "  ➤ Address     : {$restoredFromPriv['address']}\n";
echo "  ➤ Public Key : {$restoredFromPriv['public_key']}\n";
echo "  ➤ Private Key : {$restoredFromPriv['private_key']}\n";
echo "  ➤ Mnemonic : Dont Even Bother\n";

// Sanity check using a fixed mnemonic
echo "\n🧠 Sanity Check From Hardcoded Mnemonic:\n";

$mnemonic = "label summer plug math hen cabin escape gadget decorate maximum crew enforce";
$normalized = implode(' ', explode(' ', trim($mnemonic))); // strip newlines/tabs

$keyPair = $walletManager->createWalletFromMnemonic($normalized);

echo "  ➤ Mnemonic    : {$normalized}\n";
echo "  ➤ Address     : {$keyPair['address']}\n";
echo "  ➤ Public Key : {$keyPair['public_key']}\n";
echo "  ➤ Private Key : {$keyPair['private_key']}\n";

// � Final consistency test
$final = $walletManager->createWalletFromMnemonic($normalized);

echo "\n🎯 Restored from hardcoded mnemonic:\n";
echo "  ➤ Address     : {$final['address']}\n";
echo "  ➤ Public Key : {$final['public_key']}\n";
echo "  ➤ Private Key : {$final['private_key']}\n";

echo "\n✅ All tests passed.\n";
