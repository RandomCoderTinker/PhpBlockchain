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

	public function __construct()
	{
	}

	public function getAllBlocks(): array
	{
		return $this->chain;
	}

	public function addBlock(BlockInterface $block): bool
	{
		// Check if the block is valid
		if (!$this->isBlockValid($block)) {
			Logger::getInstance()->error("Block is invalid");

			return FALSE;
		}
		$this->chain[] = $block;

		return TRUE;
	}

	private function isBlockValid(BlockInterface $block): bool
	{
		// @todo implement this
		return TRUE;
	}

	public function getBlock(int $index): ?Block
	{
		// TODO: Implement getBlock() method.
	}

	public function getBlockByHash(string $hash): ?Block
	{
		// TODO: Implement getBlockByHash() method.
	}

	public function getLatestBlock(): ?Block
	{
		// TODO: Implement getLatestBlock() method.
	}

	public function getHeight(): int
	{
		// TODO: Implement getHeight() method.
	}

	public function isValid(): bool
	{
		// TODO: Implement isValid() method.
	}

}