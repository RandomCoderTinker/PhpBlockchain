<?php

require "vendor/autoload.php";

use Chain\Wallet\WalletManager;
use Chain\Cryptography\Signature;

// Instantiate the WalletManager (handles key generation, address derivation, etc.)
$wallet = new WalletManager();

// === Create a new test wallet (with public/private key pair) ===
$wallet = $wallet->createWallet();
print_r($wallet); // Outputs wallet details: address, public_key, private_key

// === Message to be signed ===
$messageToSign = "This is a test message that we signing and verifying";

// === Tampered version of the same message ===
// Uses a *Cyrillic "а"* in "messаge" instead of Latin "a" (homoglyph attack simulation)
// This causes the underlying byte string to differ even though it looks visually identical
$messageToSign_fail = "This is a test messаge that we signing and verifying";

// === Sign the message with the wallet's private key ===
// Signature typically contains r, s, and v (recovery byte)
$sign = Signature::sign($messageToSign, $wallet['private_key']);
print_r($sign); // Displays signature data

// === Verify the valid signature ===
// This should return true since the original message is used
$verify = Signature::verify($messageToSign, $sign['signature'], $wallet['public_key']);
echo "Signature valid? " . ($verify ? "Yep ✅" : "Nay ❌") . "\n";

// === Attempt to verify using the tampered message ===
// Even a single byte difference (like a Cyrillic character) causes verification to fail
$verify = Signature::verify($messageToSign_fail, $sign['signature'], $wallet['public_key']);
echo "Signature valid? " . ($verify ? "Yep ✅" : "Nay ❌") . "\n";

// === Recover the signing address from the signature ===
// Useful for stateless validation: confirms which wallet signed the message
$recoverAddress = Signature::recoverAddress($messageToSign, $sign['signature']);
print_r($recoverAddress); // Should match $wallet['address'] if valid
