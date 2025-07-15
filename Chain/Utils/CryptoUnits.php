<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Utils;

class CryptoUnits
{
	/** Number of decimals (18 for Ethereum: 1 ETH = 10^18 wei) */
	public const BASE_DECIMALS = 18;

	/** Pre-computed constant: 10^BASE_DECIMALS as string */
	private const BASE_UNIT = '1000000000000000000';

	/**
	 * Convert base units to decimal format.
	 */
	public static function fromBaseUnit(string $wei): string
	{
		if (!self::isBaseUnit($wei)) {
			throw new \InvalidArgumentException("Invalid base-unit amount: {$wei}");
		}

		$quotient = bcdiv($wei, self::BASE_UNIT, self::BASE_DECIMALS);

		return rtrim(rtrim($quotient, '0'), '.') ?: '0';
	}

	/**
	 * Check if the given string is in base unit format (integer only)
	 */
	public static function isBaseUnit(string $value): bool
	{
		// Treat as base unit only if it's numeric AND:
		// - greater than 1e5 (to avoid misclassifying ETH-scale decimals)
		// - or it's 0
		return preg_match('/^\d+$/', $value) === 1
			&& ($value === "0" || (int)$value > 100000);
	}

	/**
	 * Add two base-unit values.
	 */
	public static function add(string $a, string $b): string
	{
		if (!self::isBaseUnit($a) || !self::isBaseUnit($b)) {
			throw new \InvalidArgumentException("Both values must be in base-unit format.");
		}

		return bcadd($a, $b, 0);
	}

	/**
	 * Subtract two base-unit values.
	 */
	public static function subtract(string $a, string $b): string
	{
		if (!self::isBaseUnit($a) || !self::isBaseUnit($b)) {
			throw new \InvalidArgumentException("Both values must be in base-unit format.");
		}

		return bcsub($a, $b, 0);
	}

	/**
	 * Multiply two values (decimal or base), returning base-unit.
	 */
	public static function multiply(string $x, string $y): string
	{
		$xWei = self::normalizeToBaseUnit($x);
		$yWei = self::normalizeToBaseUnit($y);
		$product = bcmul($xWei, $yWei, 0);

		return bcdiv($product, self::BASE_UNIT, 0);
	}

	/**
	 * Normalize a value into base unit format.
	 * Accepts either decimal or base-unit format.
	 */
	public static function normalizeToBaseUnit(string $value): string
	{
		return self::isBaseUnit($value) ? $value : self::toBaseUnit($value);
	}

	/**
	 * Convert a decimal string to base units (wei).
	 */
	public static function toBaseUnit(string $amount): string
	{
		if (!preg_match('/^\d+(\.\d+)?$/', $amount)) {
			throw new \InvalidArgumentException("Invalid decimal amount: {$amount}");
		}

		[$intPart, $fracPart] = array_pad(explode('.', $amount, 2), 2, '');
		$fracPart = str_pad(substr($fracPart, 0, self::BASE_DECIMALS), self::BASE_DECIMALS, '0');
		$weiInt = bcmul($intPart, self::BASE_UNIT, 0);

		return bcadd($weiInt, $fracPart, 0);
	}

	/**
	 * Divide two base-unit values, returns decimal string.
	 */
	public static function divide(string $a, string $b, int $scale = self::BASE_DECIMALS): string
	{
		if (!self::isBaseUnit($a) || !self::isBaseUnit($b)) {
			throw new \InvalidArgumentException("Both values must be in base-unit format.");
		}
		if ($b === '0') {
			throw new \InvalidArgumentException("Division by zero");
		}

		return rtrim(rtrim(bcdiv($a, $b, $scale), '0'), '.') ?: '0';
	}

	/**
	 * Compare two base-unit values.
	 */
	public static function compare(string $a, string $b): int
	{
		if (!self::isBaseUnit($a) || !self::isBaseUnit($b)) {
			throw new \InvalidArgumentException("Both values must be in base-unit format.");
		}

		return bccomp($a, $b, 0);
	}

}
