<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Blockchain;

use Chain\Utils\Logger;
use Chain\Interfaces\BlockInterface;
use Chain\Interfaces\BlockchainInterface;

class Blockchain implements BlockchainInterface
{
	private array $chain = [];

	public function __construct() { }

	public function getAllBlocks(): array
	{
		return $this->chain;
	}

	public function addBlock(BlockInterface $block): bool
	{
		if (!$this->isBlockValid($block)) {
			Logger::getInstance()->error("❌ Block is invalid.");

			return FALSE;
		}

		$this->chain[] = $block;
		Logger::getInstance()->info("✅ Block #{$block->getHash()} added.");

		return TRUE;
	}

	private function isBlockValid(BlockInterface $block): bool
	{
		$latest = $this->getLatestBlock();
		if ($latest && $block->getPreviousHash() !== $latest->getHash()) {
			Logger::getInstance()->warning("Block previous hash mismatch.");

			return FALSE;
		}

		return $block->isValid();
	}

	public function getLatestBlock(): ?BlockInterface
	{
		return end($this->chain) ?: NULL;
	}

	public function isValid(): bool
	{
		for ($i = 1; $i < count($this->chain); $i++) {
			$prev = $this->chain[$i - 1];
			$curr = $this->chain[$i];

			if ($curr->getPreviousHash() !== $prev->getHash()) {
				return FALSE;
			}
			if (!$curr->isValid()) {
				return FALSE;
			}
		}

		return TRUE;
	}

	public function getBlock(int $index): ?BlockInterface
	{
		return $this->chain[$index] ?? NULL;
	}

	public function getBlockByHash(string $hash): ?BlockInterface
	{
		foreach ($this->chain as $block) {
			if ($block->getHash() === $hash) {
				return $block;
			}
		}

		return NULL;
	}

	public function getHeight(): int
	{
		return count($this->chain);
	}

}
