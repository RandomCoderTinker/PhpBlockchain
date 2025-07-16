<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Interfaces;

use Chain\Blockchain\Block;

interface BlockchainInterface
{
	public function addBlock(BlockInterface $block): bool;

	public function getBlock(int $index): ?Block;

	public function getBlockByHash(string $hash): ?Block;

	public function getLatestBlock(): ?Block;

	public function getHeight(): int;

	public function isValid(): bool;

}