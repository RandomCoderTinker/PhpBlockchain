<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

namespace Chain\Interfaces;

interface BlockInterface
{
	public function getHash(): string;

	public function getPreviousHash(): string;

	public function getTimestamp(): int;

	public function getTransactions(): array;

	public function isValid(): bool;

	public function getNonce(): int;

	public function sign(string $privateKey): string;

	public function verify(string $publicKey): bool;

}