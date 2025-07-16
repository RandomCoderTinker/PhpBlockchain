<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Blockchain;

use Chain\Utils\Hex;
use kornrunner\Keccak;
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
		string $previousHash)
	{
		$this->index = $index;
		$this->transactions = $transactions;
		$this->previousHash = $previousHash;
		$this->timestamp = time();
		$this->nonce = random_int(0, 1000);

		// 1) Build Merkle root over raw Transaction objects
		$this->calculateMerkleRoot();

		// 2) Compute header hash from index,timestamp,roots,nonce,gas…
		$this->calculateHash();
	}

	private function calculateMerkleRoot(): void
	{
		// MerkleTree now accepts Transaction instances directly
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

	/*** Interface implementation ***/

	public function getHash(): string
	{
		// TODO: Implement getHash() method.
	}

	public function getPreviousHash(): string
	{
		// TODO: Implement getPreviousHash() method.
	}

	public function getTimestamp(): int
	{
		// TODO: Implement getTimestamp() method.
	}

	public function getTransactions(): array
	{
		// TODO: Implement getTransactions() method.
	}

	public function isValid(): bool
	{
		// TODO: Implement isValid() method.
	}

	public function getNonce(): int
	{
		// TODO: Implement getNonce() method.
	}

	public function sign(string $privateKey): string
	{
		// TODO: Implement sign() method.
	}

	public function verify(string $publicKey): bool
	{
		// TODO: Implement verify() method.
	}

}