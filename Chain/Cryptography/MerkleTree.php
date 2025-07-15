<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

namespace Chain\Cryptography;

use Chain\Utils\Hex;
use kornrunner\Keccak;
use Chain\Transaction\Transaction;

/**
 * Merkle Tree Implementation
 *
 * Creates and verifies Merkle trees for transaction verification. This class is designed
 * for production use, providing secure hashing with domain separation, deterministic
 * serialization, and comprehensive validation.
 */
class MerkleTree
{
	/** @var string[] Array of leaf hashes */
	private array $leaves;

	/** @var array[] Multi-dimensional array representing the Merkle tree levels */
	private array $tree;

	/** @var string The Merkle root hash */
	private string $root;

	/**
	 * Constructor to initialize the Merkle tree with optional data
	 *
	 * @param array $data Array of data items to build the tree from
	 */
	public function __construct(array $data = [])
	{
		$this->leaves = [];
		$this->tree = [];

		if (!empty($data)) {
			$this->buildTree($data);
		}
	}

	/**
	 * Build Merkle tree from data array
	 *
	 * Constructs the tree by hashing leaves with domain separation and combining nodes
	 * into a single root hash.
	 *
	 * @param array $data Array of data items to build the tree from (strings, arrays, or objects)
	 * @return string The Merkle root
	 */
	public function buildTree(array $data): string
	{
		if (empty($data)) {
			$this->root = str_repeat('0', 64); // Default empty tree root (256-bit zero hash)

			return $this->root;
		}

		$this->leaves = [];
		foreach ($data as $index => $item) {
			try {
				if ($item instanceof Transaction) {
					$item = $item->getHash(); // Use transaction hash for Transaction objects
				} else if (is_array($item) || is_object($item)) {
					$item = json_encode(
						self::normalize($item),
						JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION
					);
					if ($item === FALSE) {
						throw new \InvalidArgumentException("Failed to JSON-encode item at index $index");
					}
				}
				$this->leaves[] = Hex::keccak256('LEAF' . $item);
			} catch (\Exception $e) {
				throw new \InvalidArgumentException("Invalid data item at index $index: " . $e->getMessage());
			}
		}

		$currentLevel = $this->leaves;
		$this->tree = [$currentLevel];

		while (count($currentLevel) > 1) {
			$nextLevel = [];
			for ($i = 0; $i < count($currentLevel); $i += 2) {
				$left = $currentLevel[$i];
				$right = $currentLevel[$i + 1] ?? $left; // Duplicate last node if odd count
				$nextLevel[] = Hex::keccak256('MERKLE' . $left . $right);
			}
			$currentLevel = $nextLevel;
			$this->tree[] = $currentLevel;
		}

		$this->root = $currentLevel[0];

		return $this->root;
	}

	/**
	 * Normalize array or object for consistent JSON encoding
	 *
	 * Ensures deterministic serialization by sorting array keys recursively.
	 *
	 * @param mixed $input The input to normalize (array, object, or scalar)
	 * @return mixed The normalized input
	 */
	private static function normalize($input)
	{
		if (is_array($input)) {
			ksort($input);
			foreach ($input as &$val) {
				$val = self::normalize($val);
			}
			unset($val); // Unset reference after loop
		}

		return $input;
	}

	/**
	 * Static method to create a Merkle root from transaction hashes
	 *
	 * @param array $transactionHashes Array of transaction hashes or data
	 * @return string The Merkle root
	 */
	public static function createRoot(array $transactionHashes): string
	{
		if (empty($transactionHashes)) {
			return str_repeat('0', 64);
		}

		$tree = new self($transactionHashes);

		return $tree->getRoot();
	}

	/**
	 * Get Merkle root
	 *
	 * @return string The Merkle root hash, or empty string if not built
	 */
	public function getRoot(): string
	{
		return $this->root ?? '';
	}

	/**
	 * Static method to verify a transaction in a block
	 *
	 * @param string $transactionHash The transaction hash to verify
	 * @param array  $proof           The Merkle proof
	 * @param string $merkleRoot      The expected Merkle root
	 * @return bool True if the transaction is verified, false otherwise
	 */
	public static function verifyTransaction(string $transactionHash, array $proof, string $merkleRoot): bool
	{
		return self::verifyProof($transactionHash, $proof, $merkleRoot);
	}

	/**
	 * Verify a Merkle proof
	 *
	 * Reconstructs the root hash from a leaf and its proof, comparing it to the expected root.
	 *
	 * @param string $leaf  The leaf hash to verify
	 * @param array  $proof The Merkle proof
	 * @param string $root  The expected Merkle root
	 * @return bool True if the proof is valid, false otherwise
	 * @throws \InvalidArgumentException If proof format is invalid
	 */
	public static function verifyProof(string $leaf, array $proof, string $root): bool
	{
		$currentHash = $leaf;
		foreach ($proof as $proofElement) {
			if (!isset($proofElement['hash'], $proofElement['direction'])) {
				throw new \InvalidArgumentException("Invalid proof element: missing 'hash' or 'direction'");
			}
			$siblingHash = $proofElement['hash'];
			$direction = $proofElement['direction'];

			if (!in_array($direction, ['left', 'right'], TRUE)) {
				throw new \InvalidArgumentException("Invalid proof direction: {$direction}");
			}

			$currentHash = Hex::keccak256(
				'MERKLE' .
				($direction === 'left'
					? $siblingHash . $currentHash
					: $currentHash . $siblingHash)
			);

		}

		return $currentHash === $root;
	}

	/**
	 * Create a proof for a transaction in a block
	 *
	 * @param array $transactionHashes Array of transaction hashes or data
	 * @param int   $transactionIndex  The index of the transaction
	 * @return array The Merkle proof
	 * @throws \Exception If index is out of bounds
	 */
	public static function createTransactionProof(array $transactionHashes, int $transactionIndex): array
	{
		$tree = new self($transactionHashes);

		return $tree->getProof($transactionIndex);
	}

	/**
	 * Get Merkle proof for a specific leaf
	 *
	 * Generates a proof consisting of sibling hashes and their directions needed to
	 * verify a leaf's inclusion in the tree.
	 *
	 * @param int $index The index of the leaf to get the proof for
	 * @return array The Merkle proof, where each element is ['hash' => string, 'direction' => 'left'|'right']
	 * @throws \Exception If index is out of bounds
	 */
	public function getProof(int $index): array
	{
		if ($index < 0 || $index >= count($this->leaves)) {
			throw new \Exception("Index out of bounds");
		}

		$proof = [];
		$currentIndex = $index;

		for ($level = 0; $level < count($this->tree) - 1; $level++) {
			$currentLevelNodes = $this->tree[$level];
			$isRightNode = ($currentIndex % 2) === 1;
			$siblingIndex = $isRightNode ? $currentIndex - 1 : $currentIndex + 1;

			if ($siblingIndex < count($currentLevelNodes)) {
				// real sibling
				$siblingHash = $currentLevelNodes[$siblingIndex];
				$direction = $isRightNode ? 'left' : 'right';
			} else {
				// missing sibling: use self as sibling
				$siblingHash = $currentLevelNodes[$currentIndex];
				// if we’re the left node, sibling goes “right”,
				// if we’re the right node (which won’t happen when missing), go “left”
				$direction = $isRightNode ? 'left' : 'right';
			}

			$proof[] = [
				'hash' => $siblingHash,
				'direction' => $direction,
			];

			// move up one level
			$currentIndex = intdiv($currentIndex, 2);
		}

		return $proof;
	}

	/**
	 * Combine two Merkle roots into a single root
	 *
	 * @param string $leftRoot  The left Merkle root
	 * @param string $rightRoot The right Merkle root
	 * @return string The combined Merkle root
	 */
	public static function combineRoots(string $leftRoot, string $rightRoot): string
	{
		return Hex::keccak256('MERKLE' . $leftRoot . $rightRoot);
	}

	/**
	 * Run a self-test to verify Merkle tree functionality
	 *
	 * Tests tree construction, proof generation, and verification with sample data.
	 *
	 * @return bool True if all tests pass, false otherwise
	 */
	public static function selfTest(): bool
	{
		$txs = ['tx1', 'tx2', 'tx3', 'tx3', 'tx3', 'tx3', 'tx3', 'tx3', 'tx3', 'tx3'];
		$tree = new self($txs);
		$root = $tree->getRoot();

		foreach ($txs as $i => $tx) {
			// Reconstruct the leaf exactly as it was built in buildTree()
			if (is_array($tx) || is_object($tx)) {
				$tx = json_encode(self::normalize($tx), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION);
			}
			$leaf = Hex::keccak256('LEAF' . $tx);

			$proof = $tree->getProof($i);

			if (!self::verifyProof($leaf, $proof, $root)) {
				echo "❌ Proof failed at index {$i}\n";

				return FALSE;
			}
		}

		return TRUE;
	}

	public static function hashLeaf($data): string
	{
		if (is_array($data) || is_object($data)) {
			$data = json_encode(self::normalize($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION);
		}

		return Hex::keccak256('LEAF' . $data);
	}

	/**
	 * Verify a leaf exists in the tree at a specific index
	 *
	 * @param string $data  The original data to verify
	 * @param int    $index The index of the leaf
	 * @return bool True if the leaf matches the data at the index, false otherwise
	 */
	public function verifyLeaf(string $data, int $index): bool
	{
		if ($index < 0 || $index >= count($this->leaves)) {
			return FALSE;
		}

		$expectedHash = Hex::keccak256('LEAF' . $data);

		return $this->leaves[$index] === $expectedHash;
	}

	/**
	 * Get all leaf hashes
	 *
	 * @return string[] Array of leaf hashes
	 */
	public function getLeaves(): array
	{
		return $this->leaves;
	}

	/**
	 * Get the full tree structure
	 *
	 * @return array[] The tree structure as an array of levels
	 */
	public function getTree(): array
	{
		return $this->tree;
	}

	/**
	 * Get the depth of the tree
	 *
	 * @return int The number of levels in the tree
	 */
	public function getDepth(): int
	{
		return count($this->tree);
	}

	/**
	 * Create a sparse Merkle tree for efficient updates
	 *
	 * Note: This is a basic implementation. For production use with large datasets,
	 * consider a dedicated SparseMerkleTree class with optimized storage.
	 *
	 * @param array $keyValuePairs Array of key-value pairs
	 * @param int   $depth         The depth of the sparse tree (default 256)
	 * @return string The root of the sparse tree
	 */
	public function createSparseTree(array $keyValuePairs, int $depth = 256): string
	{
		$defaultHashes = $this->generateDefaultHashes($depth);
		$root = $defaultHashes[0]; // Root is at index 0

		foreach ($keyValuePairs as $key => $value) {
			$keyBits = $this->toBinary((string)$key, $depth);
			$valueHash = Hex::keccak256('LEAF' . (string)$value);
			$root = $this->updateSparseTree($keyBits, $valueHash, 0, $defaultHashes);
		}

		return $root;
	}

	/**
	 * Generate default hashes for a sparse tree
	 *
	 * @param int $depth The depth of the sparse tree
	 * @return string[] Array of default hashes for each level
	 */
	public function generateDefaultHashes(int $depth = 256): array
	{
		$defaults = [];
		$defaults[$depth] = str_repeat('0', 64); // Empty leaf

		for ($i = $depth - 1; $i >= 0; $i--) {
			$defaults[$i] = Hex::keccak256('MERKLE' . $defaults[$i + 1] . $defaults[$i + 1]);
		}

		return $defaults;
	}

	/**
	 * Convert a key to a binary string
	 *
	 * Uses the SHA-256 hash of the key to generate a binary string of specified length.
	 *
	 * @param string $key   The key to convert
	 * @param int    $depth The depth of the tree (length of binary string)
	 * @return string Binary string representation of the key
	 */
	private function toBinary(string $key, int $depth): string
	{
		$hash = Keccak::hash($key, 256, TRUE); // Raw binary output
		$binary = '';
		for ($i = 0; $i < $depth; $i++) {
			$byteIndex = intdiv($i, 8);
			$bitIndex = $i % 8;
			$byte = ord($hash[$byteIndex]);
			$bit = ($byte >> (7 - $bitIndex)) & 1;
			$binary .= $bit;
		}

		return $binary;
	}

	/**
	 * Update a sparse Merkle tree with a key-value pair
	 *
	 * @param string   $keyBits       Binary representation of the key
	 * @param string   $valueHash     Hash of the value
	 * @param int      $currentDepth  Current depth in the tree
	 * @param string[] $defaultHashes Default hashes for each level
	 * @return string The updated hash
	 */
	public function updateSparseTree(string $keyBits, string $valueHash, int $currentDepth, array $defaultHashes): string
	{
		if ($currentDepth === strlen($keyBits)) {
			return $valueHash;
		}

		$bit = $keyBits[$currentDepth];
		$nextDepth = $currentDepth + 1;

		if ($bit === '0') {
			$left = $this->updateSparseTree($keyBits, $valueHash, $nextDepth, $defaultHashes);
			$right = $defaultHashes[$nextDepth];
		} else {
			$left = $defaultHashes[$nextDepth];
			$right = $this->updateSparseTree($keyBits, $valueHash, $nextDepth, $defaultHashes);
		}

		return Hex::keccak256('MERKLE' . $left . $right);
	}

}