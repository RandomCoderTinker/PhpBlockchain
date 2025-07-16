<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Blockchain;

use Chain\Utils\Hex;
use Chain\Cryptography\Signature;
use Chain\Cryptography\MerkleTree;
use Chain\Interfaces\BlockInterface;
use Chain\Interfaces\TransactionInterface;

/**
 * Class Block
 *
 * Represents a blockchain block with DPoS validator signature,
 * Merkle root, hash calculation, transaction and fee tracking.
 */
class Block implements BlockInterface
{
	protected string $previousHash;
	protected array $transactions;
	protected string $merkleRoot;
	protected int $timestamp;
	protected int $nonce;
	protected string $hash;
	protected int $totalGasUsedUnits;
	protected string $totalFeesCollected;
	protected string $validatorAddress;
	protected string $validatorSignature;

	/**
	 * Block constructor.
	 *
	 * @param string                 $previousHash     Hash of the previous block
	 * @param TransactionInterface[] $transactions     Validated transaction list
	 * @param string                 $validatorAddress Validator wallet address
	 * @param string                 $validatorPrivKey Validator's private key for signing
	 */
	public function __construct(
		string $previousHash,
		array  $transactions,
		string $validatorAddress,
		string $validatorPrivKey
	)
	{
		$this->previousHash = $previousHash;
		$this->transactions = $transactions;
		$this->timestamp = time();
		$this->nonce = 0;
		$this->merkleRoot = $this->buildMerkleRoot();
		$this->calculateGasAndFees();
		$this->hash = $this->calculateHash();

		$this->validatorAddress = strtolower($validatorAddress);
		$payload = $this->getSigningPayload();
		$this->validatorSignature = Signature::sign($payload, $validatorPrivKey)['signature'];
	}

	protected function buildMerkleRoot(): string
	{
		// MerkleTree now accepts Transaction instances directly
		$merkle = new MerkleTree($this->transactions);
		$this->merkleRoot = $merkle->getRoot();

		return $this->merkleRoot;
	}

	protected function calculateGasAndFees(): void
	{
		$unitsSum = 0;
		$feesSum = '0';
		foreach ($this->transactions as $tx) {
			if (!$tx->isValid()) continue;
			$units = $tx->estimateIntrinsicGas();
			$unitsSum += $units;
			$feeWei = bcmul((string)$units, $tx->getGasPrice(), 0);
			$feesSum = bcadd($feesSum, $feeWei, 0);
		}
		$this->totalGasUsedUnits = $unitsSum;
		$this->totalFeesCollected = $feesSum;
	}

	public function isValid(string $prevHash): bool
	{
		// 1. Previous hash match
		if ($prevHash !== $this->previousHash) {
			return FALSE;
		}
		// 2. Merkle root
		if ($this->buildMerkleRoot() !== $this->merkleRoot) {
			return FALSE;
		}
		// 3. Block hash
		if ($this->calculateHash() !== $this->hash) {
			return FALSE;
		}
		// 4. Transaction validity
		foreach ($this->transactions as $tx) {
			if (!$tx->isValid()) {
				return FALSE;
			}
		}
		// 5. Validator signature
		$recovered = Signature::recoverAddress(
			$this->getSigningPayload(),
			$this->validatorSignature
		);

		return strtolower($recovered) === strtolower($this->validatorAddress);
	}

	protected function calculateHash(): string
	{
		$header = json_encode([
			'previousHash' => $this->previousHash,
			'merkleRoot' => $this->merkleRoot,
			'timestamp' => $this->timestamp,
			'nonce' => $this->nonce,
		], JSON_UNESCAPED_SLASHES);

		return Hex::keccak256($header);
	}

	public function getSigningPayload(): string
	{
		$header = [
			'previousHash' => $this->previousHash,
			'merkleRoot' => $this->merkleRoot,
			'timestamp' => $this->timestamp,
			'nonce' => $this->nonce,
			'validatorAddress' => $this->validatorAddress,
		];

		return json_encode($header, JSON_UNESCAPED_SLASHES);
	}

	public function getPreviousHash(): string
	{
		return $this->previousHash;
	}

	public function getMerkleRoot(): string
	{
		return $this->merkleRoot;
	}

	public function getHash(): string
	{
		return $this->hash;
	}

	public function getTimestamp(): int
	{
		return $this->timestamp;
	}

	public function getNonce(): int
	{
		return $this->nonce;
	}

	public function getTransactions(): array
	{
		return $this->transactions;
	}

	public function getTotalGasUsedUnits(): int
	{
		return $this->totalGasUsedUnits;
	}

	public function getTotalFeesCollected(): string
	{
		return $this->totalFeesCollected;
	}

	public function getValidatorAddress(): string
	{
		return $this->validatorAddress;
	}

	public function getValidatorSignature(): string
	{
		return $this->validatorSignature;
	}

	public function toArray(): array
	{
		return [
			'previousHash' => $this->previousHash,
			'merkleRoot' => $this->merkleRoot,
			'timestamp' => $this->timestamp,
			'nonce' => $this->nonce,
			'transactions' => array_map(fn($tx) => $tx->toArray(), $this->transactions),
			'totalGasUsedUnits' => $this->totalGasUsedUnits,
			'totalFeesCollected' => $this->totalFeesCollected,
			'hash' => $this->hash,
			'validatorAddress' => $this->validatorAddress,
			'validatorSignature' => $this->validatorSignature,
		];
	}

}
