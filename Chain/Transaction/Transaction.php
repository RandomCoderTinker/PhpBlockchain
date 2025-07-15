<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

namespace Chain\Transaction;

use kornrunner\Keccak;

class Transaction
{

	/**
	 * Generates a random hash based on random data
	 * Used as a template for testing merkle tree
	 *
	 * @return string
	 */
	public function getHash(): string
	{
		$randomBytes = random_bytes(16);
		$message = bin2hex($randomBytes);

		return Keccak::hash($message, 256);
	}

}