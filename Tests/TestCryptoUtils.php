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
 * Outputs a formatted test result.
 *
 * @param string $name   The name of the test
 * @param bool   $passed Whether the test passed
 */
function testResult(string $name, bool $passed): void
{
	echo $passed ? "✅ PASS: $name\n" : "❌ FAIL: $name\n";
}

/**
 * Runs a suite of deterministic unit tests for the CryptoUnits utility class.
 *
 * These tests validate:
 * - Conversion between decimals and base units (wei)
 * - Arithmetic operations in base units
 * - Input validation for base-unit format
 * - Reversibility of conversions
 * - Safe error handling for invalid inputs
 */
function runCryptoUnitsTests(): void
{
	echo "=== Running CryptoUnits Tests ===\n";

	// Test decimal to base unit conversion
	try {
		testResult("toBaseUnit 1.5", CryptoUnits::toBaseUnit("1.5") === "1500000000000000000");
		testResult("toBaseUnit 0.000000015", CryptoUnits::toBaseUnit("0.000000015") === "15000000000");
		testResult("toBaseUnit 0", CryptoUnits::toBaseUnit("0") === "0");

		$errorCaught = FALSE;
		try {
			CryptoUnits::toBaseUnit("abc"); // Invalid format
		} catch (InvalidArgumentException) {
			$errorCaught = TRUE;
		}
		testResult("toBaseUnit invalid", $errorCaught);
	} catch (Exception $e) {
		echo "❌ ERROR in toBaseUnit: " . $e->getMessage() . "\n";
	}

	// Test base unit to decimal conversion
	testResult("fromBaseUnit 1500000000000000000", CryptoUnits::fromBaseUnit("1500000000000000000") === "1.5");
	testResult("fromBaseUnit 0", CryptoUnits::fromBaseUnit("0") === "0");

	// Test normalization (convert to base unit if not already)
	testResult("normalize base already", CryptoUnits::normalizeToBaseUnit("1000000000000000000") === "1000000000000000000");
	testResult("normalize decimal", CryptoUnits::normalizeToBaseUnit("1") === "1000000000000000000");

	// Validate format detection
	testResult("isBaseUnit valid", CryptoUnits::isBaseUnit("1230000000000000000") === TRUE);
	testResult("isBaseUnit invalid", CryptoUnits::isBaseUnit("1.23") === FALSE);

	// Test addition of base unit values
	testResult(
		"add base units",
		CryptoUnits::add("1000000000000000000", "500000000000000000") === "1500000000000000000"
	);

	// Test subtraction of base unit values
	testResult(
		"subtract base units",
		CryptoUnits::subtract("1500000000000000000", "500000000000000000") === "1000000000000000000"
	);

	// Test multiplication of decimal values, returning base unit
	testResult("multiply 1.5 x 2", CryptoUnits::multiply("1.5", "2") === "3000000000000000000");

	// Test division of base unit values
	testResult(
		"divide 3e18 / 2e18 = 1.5",
		CryptoUnits::divide("3000000000000000000", "2000000000000000000") === "1.5"
	);

	// Check divide-by-zero handling
	$errorCaught = FALSE;
	try {
		CryptoUnits::divide("3000000000000000000", "0");
	} catch (InvalidArgumentException) {
		$errorCaught = TRUE;
	}
	testResult("divide by zero", $errorCaught);

	// Test comparison of base unit values
	testResult("compare equal", CryptoUnits::compare("1000000000000000000", "1000000000000000000") === 0);
	testResult("compare a > b", CryptoUnits::compare("2000000000000000000", "1000000000000000000") === 1);
	testResult("compare a < b", CryptoUnits::compare("500000000000000000", "1000000000000000000") === -1);

	echo "=== Done ===\n";
}

/**
 * Runs a randomized fuzz test suite to catch edge cases.
 *
 * This tests:
 * - Decimal-to-wei conversion and reversibility
 * - Arithmetic integrity (add, subtract, multiply, divide)
 * - Proper base unit formatting
 * - Precision safety (no truncation, no drift)
 *
 * @param int $iterations Number of fuzz iterations to run
 * @throws \Random\RandomException
 */
function fuzzTestCryptoUnits(int $iterations = 20): void
{
	echo "\n=== Fuzz Testing CryptoUnits ($iterations iterations) ===\n";

	$passes = 0;
	$fails = 0;

	for ($i = 0; $i < $iterations; $i++) {
		// Generate two random decimal strings with safe precision
		$a = generateRandomDecimal();
		$b = generateRandomDecimal(nonZero: TRUE);

		try {
			// Convert both to base unit (wei)
			$aWei = CryptoUnits::toBaseUnit($a);
			$bWei = CryptoUnits::toBaseUnit($b);

			// Convert back to decimal
			$aBack = CryptoUnits::fromBaseUnit($aWei);
			$bBack = CryptoUnits::fromBaseUnit($bWei);

			// Perform arithmetic
			$add = CryptoUnits::add($aWei, $bWei);
			$sub = CryptoUnits::subtract($add, $bWei); // should equal aWei
			$cmp = CryptoUnits::compare($aWei, $bWei);
			$div = CryptoUnits::divide($aWei, $bWei);
			$mul = CryptoUnits::multiply($a, $b);

			// Validate all results
			$passed =
				CryptoUnits::isBaseUnit($aWei) &&
				CryptoUnits::isBaseUnit($bWei) &&
				bccomp($sub, $aWei, 0) === 0 &&
				in_array($cmp, [-1, 0, 1], TRUE) &&
				bccomp(CryptoUnits::toBaseUnit($aBack), $aWei, 0) === 0 &&
				bccomp(CryptoUnits::toBaseUnit($bBack), $bWei, 0) === 0 &&
				preg_match('/^\d+$/', $mul) === 1 &&             // ensure multiply returns base unit
				preg_match('/^\d+(\.\d+)?$/', $div) === 1;       // ensure divide returns valid decimal

			if ($passed) {
				$passes++;
			} else {
				echo "❌ Fuzz fail (case $i):\n";
				echo "   a: $a ($aWei)\n";
				echo "   b: $b ($bWei)\n";
				echo "   aBack: $aBack\n";
				echo "   bBack: $bBack\n";
				echo "   add: $add\n";
				echo "   sub: $sub\n";
				echo "   mul: $mul\n";
				echo "   div: $div\n";
				$fails++;
			}
		} catch (Exception $e) {
			echo "❌ Exception in fuzz test $i: " . $e->getMessage() . "\n";
			$fails++;
		}
	}

	echo "Fuzz Test Result: ✅ $passes passed, ❌ $fails failed\n";
}

/**
 * Generates a safe decimal string with at most 18 digits of precision.
 *
 * @param bool $nonZero If true, guarantees the integer part is non-zero
 * @return string A realistic random decimal value (up to 18 decimals)
 * @throws \Random\RandomException
 */
function generateRandomDecimal(bool $nonZero = FALSE): string
{
	$intPart = random_int($nonZero ? 1 : 0, 999_999_999_999);
	$fracDigits = random_int(1, 18);
	$fracPart = str_pad((string)random_int(0, 10 ** $fracDigits - 1), $fracDigits, '0', STR_PAD_LEFT);

	return "$intPart.$fracPart";
}

// Run all deterministic and fuzz tests
runCryptoUnitsTests();
fuzzTestCryptoUnits(50);
