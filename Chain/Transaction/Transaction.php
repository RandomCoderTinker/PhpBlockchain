<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

namespace Chain\Transaction;

use Chain\Utils\Hex;
use Chain\Utils\CryptoUnits;
use Chain\Cryptography\Signature;
use Chain\Interfaces\TransactionInterface;

class Transaction implements TransactionInterface
{

	protected string $from;
	protected string $to;
	protected string $amount;
	protected string $fee;
	protected int $timestamp;
	protected int $nonce;
	protected string $gasLimit;
	protected string $gasPrice;
	protected string $signature = '';
	protected ?string $data;

	/**
	 * Transaction constructor.
	 *
	 * @param string      $from     Sender address (hex)
	 * @param string      $to       Recipient address (hex)
	 * @param string      $amount   Amount in ETH-style decimal (e.g. "0.01")
	 * @param int         $nonce    Nonce
	 * @param string|null $data     Optional hex-encoded payload (0x...)
	 * @param string      $gasPrice Gas price in ETH-style decimal (e.g. "0.00000001")
	 */
	public function __construct(
		string  $from,
		string  $to,
		string  $amount,
		int     $nonce,
		?string $data = NULL,
		string  $gasPrice = "0"
	)
	{
		$this->from = strtolower($from);
		$this->to = strtolower($to);
		$this->amount = CryptoUnits::toBaseUnit($amount);
		$this->gasPrice = CryptoUnits::toBaseUnit($gasPrice);
		$this->nonce = $nonce;
		$this->data = $data;
		$this->timestamp = time();

		// 1) Auto-estimate intrinsic gas from data
		$this->gasLimit = (string)$this->estimateIntrinsicGas();

		// 2) Auto-calculate fee = gasLimit × gasPrice (wei)
		$this->fee = bcmul($this->gasLimit, $this->gasPrice, 0);
	}

	/**
	 * Estimate intrinsic gas: 21k base + 4/16 per data byte
	 */
	public function estimateIntrinsicGas(): int
	{
		$baseGas = 21000;
		if (empty($this->data)) {
			return $baseGas;
		}

		$data = $this->data;
		if (str_starts_with($data, '0x')) {
			$data = substr($data, 2);
		}

		if (!ctype_xdigit($data)) {
			throw new \InvalidArgumentException("Invalid hex data payload");
		}

		$bytes = str_split(hex2bin($data));
		$gas = $baseGas;
		foreach ($bytes as $b) {
			$gas += ord($b) === 0 ? 4 : 16;
		}

		return $gas;
	}

	/**
	 * Get the transaction hash (keccak256 of JSON payload)
	 */
	public function getHash(): string
	{
		$txData = json_encode($this->toArray());

		return Hex::keccak256($txData);
	}

	/**
	 * @inheritdoc
	 */
	public function toArray(): array
	{
		// raw units × price → wei
		$gasUsedWei = bcmul($this->gasLimit, $this->gasPrice, 0);

		return [
			'from' => $this->from,
			'to' => $this->to,
			'amount' => CryptoUnits::fromBaseUnit($this->amount),
			'fee' => CryptoUnits::fromBaseUnit($this->fee),
			'nonce' => $this->nonce,
			'data' => $this->data,
			'gasLimit' => $this->gasLimit,
			'gasPrice' => CryptoUnits::fromBaseUnit($this->gasPrice),
			'gasUsed' => $this->gasLimit,
			'feeWei' => $gasUsedWei,
			'feeDecimal' => CryptoUnits::fromBaseUnit($gasUsedWei),
			'timestamp' => $this->timestamp,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getFrom(): string
	{
		return $this->from;
	}

	/**
	 * Recover and return the sender address from signature
	 */
	public function getSender(): ?string
	{
		if (empty($this->signature)) {
			return NULL;
		}

		return Signature::recoverAddress($this->getSigningPayload(), $this->signature);
	}

	/**
	 * Payload to sign / recover: JSON array of core fields
	 */
	public function getSigningPayload(): string
	{
		$payload = [
			$this->from,
			$this->to,
			$this->amount,
			$this->fee,
			$this->nonce,
			$this->data,
			$this->gasLimit,
			$this->gasPrice,
			$this->timestamp,
		];

		return json_encode($payload, JSON_UNESCAPED_SLASHES);
	}

	/**
	 * @inheritdoc
	 */
	public function getTo(): string
	{
		return $this->to;
	}

	/**
	 * @inheritdoc
	 */
	public function getAmount(): string
	{
		return $this->amount;
	}

	/**
	 * Get the transaction fee (wei)
	 */
	public function getFee(): string
	{
		return $this->fee;
	}

	/**
	 * Get gas limit (units)
	 */
	public function getGasLimit(): string
	{
		return $this->gasLimit;
	}

	/**
	 * Get gas price (wei)
	 */
	public function getGasPrice(): string
	{
		return $this->gasPrice;
	}

	/**
	 * @inheritdoc
	 */
	public function getTimestamp(): int
	{
		return $this->timestamp;
	}

	/**
	 * Sign the transaction using EIP-191 style prefix and secp256k1
	 */
	public function sign(string $privateKey): string
	{
		$result = Signature::sign($this->getSigningPayload(), $privateKey);
		$this->signature = $result['signature'];

		return $this->signature;
	}

	/**
	 * Verify the signature against the public key
	 */
	public function verify(string $publicKey): bool
	{
		return Signature::verify($this->getSigningPayload(), $this->signature, $publicKey);
	}

	/**
	 * Check overall transaction validity:
	 *   - intrinsic gas limit sufficient
	 *   - fee covers gasUsed
	 *   - signature recovers correct sender
	 */
	public function isValid(): bool
	{
		if (!$this->isSystemTx()) {
			$estimated = $this->estimateIntrinsicGas();
			if ((int)$this->gasLimit < $estimated) {
				return FALSE;
			}

			$gasUsed = (string)bcmul($this->gasLimit, $this->gasPrice, 0);
			if (CryptoUnits::compare($this->fee, $gasUsed) < 0) {
				return FALSE;
			}
		}

		try {
			$recovered = Signature::recoverAddress(
				$this->getSigningPayload(),
				$this->signature
			);
		} catch (\Throwable $e) {
			// invalid signature or faulty point => not valid
			return FALSE;
		}

		return strtolower((string)$recovered) === strtolower($this->from);
	}

	/**
	 * System transactions incur zero gas/fee
	 */
	protected function isSystemTx(): bool
	{
		return $this->gasPrice === '0';
	}

	/**
	 * Return JSON-string byte size (for block packing, etc.)
	 */
	public function getSize(): int
	{
		return strlen(json_encode($this->toArray()));
	}

}
