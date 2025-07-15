<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

use Chain\Utils\CryptoUnits;

require dirname(__DIR__) . "/vendor/autoload.php";

/**
 * Pretty prints the result of a test.
 */
function testResult(string $name, bool $passed): void
{
	printf("%s %s\n", $passed ? '✅ PASS' : '❌ FAIL', $name);
}

/**
 * Run deterministic unit tests for the CryptoUnits class.
 */
function runCryptoUnitsTests(): void
{
	echo "\n� Running CryptoUnits Unit Tests\n===============================\n";

	try {
		testResult("Convert 1.5 to wei", CryptoUnits::toBaseUnit("1.5") === "1500000000000000000");
		testResult("Convert 0.000000015 to wei", CryptoUnits::toBaseUnit("0.000000015") === "15000000000");
		testResult("Convert 0 to wei", CryptoUnits::toBaseUnit("0") === "0");

		$errorCaught = FALSE;
		try {
			CryptoUnits::toBaseUnit("not-a-number");
		} catch (InvalidArgumentException) {
			$errorCaught = TRUE;
		}
		testResult("Handle invalid decimal input", $errorCaught);

		testResult("Convert from wei 1.5e18", CryptoUnits::fromBaseUnit("1500000000000000000") === "1.5");
		testResult("Convert from wei 0", CryptoUnits::fromBaseUnit("0") === "0");

		testResult("Normalize base unit (no change)", CryptoUnits::normalizeToBaseUnit("1000000000000000000") === "1000000000000000000");
		testResult("Normalize decimal 1", CryptoUnits::normalizeToBaseUnit("1") === "1000000000000000000");

		testResult("Detect valid base unit", CryptoUnits::isBaseUnit("1230000000000000000"));
		testResult("Detect invalid base unit", !CryptoUnits::isBaseUnit("1.23"));

		testResult("Add base units", CryptoUnits::add("1000000000000000000", "500000000000000000") === "1500000000000000000");
		testResult("Subtract base units", CryptoUnits::subtract("1500000000000000000", "500000000000000000") === "1000000000000000000");
		testResult("Multiply decimals to wei", CryptoUnits::multiply("1.5", "2") === "3000000000000000000");
		testResult("Divide wei: 3e18 / 2e18", CryptoUnits::divide("3000000000000000000", "2000000000000000000") === "1.5");

		$errorCaught = FALSE;
		try {
			CryptoUnits::divide("1000000000000000000", "0");
		} catch (InvalidArgumentException) {
			$errorCaught = TRUE;
		}
		testResult("Divide by zero safely", $errorCaught);

		testResult("Compare equal", CryptoUnits::compare("1000000000000000000", "1000000000000000000") === 0);
		testResult("Compare a > b", CryptoUnits::compare("2000000000000000000", "1000000000000000000") === 1);
		testResult("Compare a < b", CryptoUnits::compare("500000000000000000", "1000000000000000000") === -1);
	} catch (Exception $e) {
		echo "❌ ERROR: " . $e->getMessage() . "\n";
	}

	echo "===============================\n";
}

/**
 * Perform randomized fuzz testing against the CryptoUnits class.
 */
function fuzzTestCryptoUnits(int $iterations = 50): void
{
	echo "\n🔍 Fuzz Testing CryptoUnits ({$iterations} iterations)\n==========================================\n";

	$pass = 0;
	$fail = 0;

	for ($i = 0; $i < $iterations; $i++) {
		$a = generateRandomDecimal();
		$b = generateRandomDecimal(TRUE);

		try {
			$aWei = CryptoUnits::toBaseUnit($a);
			$bWei = CryptoUnits::toBaseUnit($b);
			$aBack = CryptoUnits::fromBaseUnit($aWei);
			$bBack = CryptoUnits::fromBaseUnit($bWei);
			$add = CryptoUnits::add($aWei, $bWei);
			$sub = CryptoUnits::subtract($add, $bWei);
			$div = CryptoUnits::divide($aWei, $bWei);
			$mul = CryptoUnits::multiply($a, $b);
			$cmp = CryptoUnits::compare($aWei, $bWei);

			$passed = (
				CryptoUnits::isBaseUnit($aWei)
				&& CryptoUnits::isBaseUnit($bWei)
				&& bccomp($sub, $aWei, 0) === 0
				&& bccomp(CryptoUnits::toBaseUnit($aBack), $aWei, 0) === 0
				&& bccomp(CryptoUnits::toBaseUnit($bBack), $bWei, 0) === 0
				&& preg_match('/^\d+$/', $mul)
				&& preg_match('/^\d+(\.\d+)?$/', $div)
				&& in_array($cmp, [-1, 0, 1], TRUE)
			);

			$passed ? $pass++ : $fail++;

			if (!$passed) {
				echo "❌ Fuzz fail (case $i):\n";
				echo "   a: $a ($aWei)\n";
				echo "   b: $b ($bWei)\n";
				echo "   aBack: $aBack\n";
				echo "   bBack: $bBack\n";
				echo "   add: $add\n";
				echo "   sub: $sub\n";
				echo "   mul: $mul\n";
				echo "   div: $div\n";
			}
		} catch (Throwable $e) {
			echo "❌ Exception in fuzz case $i: " . $e->getMessage() . "\n";
			$fail++;
		}
	}

	echo "\n� Fuzz Results: $pass passed, $fail failed\n";
	echo "==========================================\n";
}

/**
 * Generate a safe random decimal with up to 18 decimal places.
 */
function generateRandomDecimal(bool $nonZero = FALSE): string
{
	$intPart = random_int($nonZero ? 1 : 0, 999999999999);
	$decPlaces = random_int(1, 18);
	$decPart = str_pad((string)random_int(0, (10 ** $decPlaces) - 1), $decPlaces, '0', STR_PAD_LEFT);

	return "$intPart.$decPart";
}

// Run full suite
runCryptoUnitsTests();
fuzzTestCryptoUnits();