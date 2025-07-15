<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use Chain\Wallet\WalletManager;

// Instantiate the WalletManager (handles key generation, address derivation, etc.)
$wallet = new WalletManager();

// === Create a new test wallet (with public/private key pair) ===
try {
	$wallet = $wallet->createWallet();
} catch (Exception $e) {
	echo "Unable to create wallet: " . $e->getMessage();
}
print_r($wallet); // Outputs wallet details: address, public_key, private_key
