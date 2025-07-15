<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

use Chain\Config\Config;

require "vendor/autoload.php";

// RPC Endpoints
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get input
$request = json_decode(file_get_contents('php://input'), TRUE);

// Start the response
$response = [
	'jsonrpc' => '2.0',
	'id' => $request['id'] ?? NULL,
];

// Ensure request is valid
if (!isset($request['method'])) {
	$response['error'] = ['code' => -32600, 'message' => 'Invalid Request: ' . Config::getInstance()->get('network.chain_id')];
	echo json_encode($response);
	exit;
}

// Dispatch method
$method = $request['method'];
$params = $request['params'] ?? [];

// Get Chain ID from config file
$chain_id = Config::getInstance()->get('network.chain_id');

// Convert to Hex
function toHex(string|int $number): string
{
	return '0x' . gmp_strval(gmp_init($number, 10), 16);
}

// Switch method for the endpoints
try {
	switch ($method) {
		case 'eth_chainId':
			$response['result'] = toHex($chain_id); // Chain ID (hex)
			break;

		case 'net_version':
			$response['result'] = $chain_id; // Network ID (decimal string)
			break;

		case 'eth_blockNumber':
			$blockNumber = getCurrentBlockNumber(); // Custom function
			$response['result'] = '0x' . dechex($blockNumber);
			break;

		case 'eth_getBlockByNumber':
			$block = getBlockByNumber($params[0], $params[1] ?? FALSE);
			$response['result'] = $block;
			break;

		case 'eth_getTransactionByHash':
			$tx = getTransactionByHash($params[0]);
			$response['result'] = $tx;
			break;

		case 'eth_getBalance':
			$response['result'] = toHex((string)getWalletBalance($params[0]));
			break;

		case 'eth_getTransactionCount':
			$nonce = getWalletNonce($params[0]);
			$response['result'] = '0x' . dechex($nonce);
			break;

		case 'eth_sendRawTransaction':
			$rawTx = $params[0];
			$txHash = '0x' . hash('sha256', $rawTx);
			$response['result'] = $txHash;
			break;

		case 'eth_getTransactionReceipt':
			$hash = $params[0];

			if (isset($_SESSION['fake_tx_pool'][$hash])) {
				$receipt = $_SESSION['fake_tx_pool'][$hash];
				$response['result'] = $receipt;
			} else {
				$response['result'] = NULL; // Not found yet
			}
			break;

		case 'eth_call':
			$result = executeReadOnlyContractCall($params[0]);
			$response['result'] = $result;
			break;

		case 'eth_estimateGas':
			$response['result'] = '0x5208'; // Dummy 21000 (gasless chain)
			break;

		case 'eth_gasPrice':
			$response['result'] = '0x0'; // Gasless
			break;

		default:
			$response['error'] = [
				'code' => -32601,
				'message' => "Method '{$method}' not found",
			];
			break;
	}

} catch (Exception $e) {
	$response['error'] = [
		'code' => -32000,
		'message' => 'Server error: ' . $e->getMessage(),
	];
}

// Encode the response
echo json_encode($response);

/**
 * Stub implementations (replace with real logic)
 */
function getCurrentBlockNumber(): int
{
	return 37429;
}

function getBlockByNumber($number, $full)
{
	return [
		'number' => $number,
		'hash' => '0xabc123...',
		'transactions' => $full ? [] : NULL,
		'timestamp' => time(),
	];
}

function getTransactionByHash($hash)
{
	return [
		'hash' => $hash,
		'from' => '0xdeadbeef...',
		'to' => '0xbeefdead...',
		'value' => '0x0',
		'nonce' => '0x1',
	];
}

function getWalletBalance($address): string
{
	return '42000000000000000000'; // 42 CB (wei)
}

function getWalletNonce($address): int
{
	return 3;
}

function executeReadOnlyContractCall($params): string
{
	return '0x';
}