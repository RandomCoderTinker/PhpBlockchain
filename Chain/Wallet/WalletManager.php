<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Wallet;

class WalletManager
{

	public function createWallet()
	{
		try {
			$mnemonic = Mnemonic::generate(); // Returns array of words
			$keyPair = KeyPair::fromMnemonic(implode(' ', $mnemonic), '');
			$address = $keyPair->getAddress();

			return [
				'address' => $address,
				'public_key' => $keyPair->getPublicKey(),
				'private_key' => $keyPair->getPrivateKey(),
				'mnemonic' => $mnemonic,
			];
		} catch (\Exception $e) {
			throw new \Exception("Failed to create wallet: " . $e->getMessage());
		}

	}

}