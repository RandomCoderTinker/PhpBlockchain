<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

namespace Chain\Interfaces;

interface TransactionInterface
{
	public function getHash(): string;

	public function getFrom(): string;

	public function getTo(): string;

	public function getAmount(): string;

	public function getTimestamp(): int;

	public function sign(string $privateKey): string;

	public function verify(string $publicKey): bool;

	public function isValid(): bool;

	public function toArray(): array;

}
