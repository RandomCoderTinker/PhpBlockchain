<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Interfaces;

interface BlockchainInterface
{
	public function addBlock(BlockInterface $block): bool;

	public function getBlock(int $index): ?BlockInterface;

	public function getBlockByHash(string $hash): ?BlockInterface;

	public function getLatestBlock(): ?BlockInterface;

	public function getHeight(): int;

	public function isValid(): bool;

}