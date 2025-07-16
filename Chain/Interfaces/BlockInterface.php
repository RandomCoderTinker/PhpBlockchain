<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

namespace Chain\Interfaces;

interface BlockInterface
{
	public function getPreviousHash(): string;

	public function getMerkleRoot(): string;

	public function getHash(): string;

	public function getTimestamp(): int;

	public function getNonce(): int;

	public function getTransactions(): array;

	public function getTotalGasUsedUnits(): int;

	public function getTotalFeesCollected(): string;

	public function isValid(string $prevHash): bool;

	public function toArray(): array;

}