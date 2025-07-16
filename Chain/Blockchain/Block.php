<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Blockchain;

use Chain\Utils\Hex;
use Chain\Cryptography\MerkleTree;
use Chain\Interfaces\BlockInterface;

class Block implements BlockInterface
{
	public int $index;
	public int $timestamp;
	public array $transactions;
	public string $previousHash;
	public string $hash;
	public int $nonce;
	public string $merkleRoot;

	public function __construct(
		int    $index,
		array  $transactions,
		string $previousHash
	)
	{
		$this->index = $index;
		$this->transactions = $transactions;
		$this->previousHash = $previousHash;
		$this->timestamp = time();
		$this->nonce = random_int(0, 1000);

		$this->calculateMerkleRoot();
		$this->calculateHash();
	}

	private function calculateMerkleRoot(): void
	{
		$merkle = new MerkleTree($this->transactions);
		$this->merkleRoot = $merkle->getRoot();
	}

	private function calculateHash(): string
	{
		$this->hash = Hex::hashArray($this->getSigningPayload());

		return $this->hash;
	}

	private function getSigningPayload(): array
	{
		return [
			'previousHash' => $this->previousHash,
			'timestamp' => $this->timestamp,
			'blockNumber' => $this->index,
			'merkleRoot' => $this->merkleRoot,
			'nonce' => $this->nonce,
		];
	}

	// --- Interface Implementation ---

	public function getHash(): string
	{
		return $this->hash;
	}

	public function getPreviousHash(): string
	{
		return $this->previousHash;
	}

	public function getTimestamp(): int
	{
		return $this->timestamp;
	}

	public function getTransactions(): array
	{
		return $this->transactions;
	}

	public function isValid(): bool
	{
		$expectedHash = Hex::hashArray($this->getSigningPayload());

		return $this->hash === $expectedHash;
	}

	public function getNonce(): int
	{
		return $this->nonce;
	}

	public function sign(string $privateKey): string
	{
		// You can swap this for real Signature::sign()
		return '0xSignedBlockPlaceholder';
	}

	public function verify(string $publicKey): bool
	{
		// Swap with Signature::verify() for real usage
		return TRUE;
	}

}
