<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

namespace Chain\Utils;

use kornrunner\Keccak;

class Hex
{
	/**
	 * Prefix a hex string with 0x if it isn't already.
	 */
	public static function prefix(string $hex): string
	{
		return str_starts_with($hex, '0x') ? $hex : '0x' . $hex;
	}

	/**
	 * Convenience: Hash an array (e.g., block or tx payload) via JSON and return 0x-hash.
	 */
	public static function hashArray(array $input): string
	{
		return self::keccak256(json_encode($input, JSON_UNESCAPED_SLASHES));
	}

	/**
	 * Keccak-256 hash a value (usually stringified JSON or raw message),
	 * and return 0x-prefixed hash string.
	 *
	 * @param string $data - raw string to hash (not hex!)
	 * @return string 0x-prefixed Keccak-256 hash
	 */
	public static function keccak256(string $data): string
	{
		return '0x' . Keccak::hash($data, 256);
	}

	/**
	 * Keccak-256 hash a value (usually stringified JSON or raw message),
	 * and return non-0x-prefixed hash string.
	 *
	 * @param string $data - raw string to hash (not hex!)
	 * @return string a non-0x-prefixed Keccak-256 hash
	 */
	public static function keccak256NoPrefix(string $data): string
	{
		return Keccak::hash($data, 256);
	}

	/**
	 * Checks if a string is a valid hex (optionally prefixed with 0x).
	 */
	public static function isHex(string $input): bool
	{
		$clean = self::strip($input);

		return ctype_xdigit($clean);
	}

	/**
	 * Remove 0x from a hex string if it exists.
	 */
	public static function strip(string $hex): string
	{
		return str_starts_with($hex, '0x') ? substr($hex, 2) : $hex;
	}

}
