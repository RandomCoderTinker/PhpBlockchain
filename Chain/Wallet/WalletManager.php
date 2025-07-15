<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Wallet;

use Chain\Utils\Hex;

class WalletManager
{

	public function createWallet(): array
	{
		try {
			$mnemonic = Mnemonic::generate();

			$keyPair = KeyPair::fromMnemonic($mnemonic, '');

			return [
				'address' => $keyPair->getAddress(),
				'public_key' => $keyPair->getPublicKey(),
				'private_key' => '0x' . $keyPair->getPrivateKey(),
				'mnemonic' => $mnemonic,
				'derivation_path' => "m/44'/60'/0'/0/0",
			];
		} catch (\Exception $e) {
			throw new \Exception("Failed to create wallet: " . $e->getMessage());
		}
	}

	public function createWalletFromMnemonic(string $mnemonic, string $password = '')
	{
		try {
			$keyPair = KeyPair::fromMnemonic($mnemonic, $password);
			$address = $keyPair->getAddress();

			return [
				'address' => $address,
				'public_key' => $keyPair->getPublicKey(),
				'private_key' => '0x' . ltrim($keyPair->getPrivateKey(), '0x'),
				'mnemonic' => $mnemonic,
			];
		} catch (\Exception $e) {
			throw new \Exception("Failed to create wallet from mnemonic: " . $e->getMessage());
		}
	}

	public function restoreWalletFromPrivateKey(string $privateKey): array
	{
		$keyPair = KeyPair::fromPrivateKey(Hex::strip($privateKey));

		return [
			'address' => $keyPair->getAddress(),
			'public_key' => $keyPair->getPublicKey(),
			'private_key' => '0x' . ltrim($keyPair->getPrivateKey(), '0x'),
			'derivation_path' => "m/44'/60'/0'/0/0",
		];

	}

}