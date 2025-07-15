<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */
declare(strict_types=1);

namespace Chain\Utils;

use Chain\Config\Config;

class Logger
{
	// PSR-style log level constants
	public const EMERGENCY = 'emergency';
	public const ALERT = 'alert';
	public const CRITICAL = 'critical';
	public const ERROR = 'error';
	public const WARNING = 'warning';
	public const NOTICE = 'notice';
	public const INFO = 'info';
	public const DEBUG = 'debug';
	public const SUCCESS = 'success';

	/**
	 * @var Logger|null Singleton instance
	 */
	private static ?Logger $instance = NULL;

	public function emergency($message, array $context = []): void
	{
		$this->log(self::EMERGENCY, $message, $context);
	}

	public function log($level, $message, array $context = []): void
	{
		$interpolated = $this->interpolate($message, $context);
		$timestamp = date('Y-m-d H:i:s');
		error_log("[$timestamp][$level] $interpolated");

		$logPath = Config::getInstance()->getLogFile();
		$dir = dirname($logPath);

		// Ensure directory exists
		if (!is_dir($dir)) {
			mkdir($dir, 0775, TRUE);
		}

		// Ensure file exists (optional: create empty file)
		if (!file_exists($logPath)) {
			touch($logPath);
		}

		// Append the log entry
		file_put_contents($logPath, "\n[$timestamp][$level] $interpolated", FILE_APPEND);
	}

	private function interpolate(string $message, array $context): string
	{
		foreach ($context as $key => $val) {
			if (is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
				$message = str_replace('{' . $key . '}', (string)$val, $message);
			}
		}

		return $message;
	}

	/**
	 * Get the singleton instance of the Logger class.
	 *
	 * @return Logger
	 */
	public static function getInstance(): Logger
	{
		if (self::$instance === NULL) {
			self::$instance = new Logger();
		}

		return self::$instance;
	}

	public function alert($message, array $context = []): void
	{
		$this->log(self::ALERT, $message, $context);
	}

	public function critical($message, array $context = []): void
	{
		$this->log(self::CRITICAL, $message, $context);
	}

	public function error($message, array $context = []): void
	{
		$this->log(self::ERROR, $message, $context);
	}

	public function warning($message, array $context = []): void
	{
		$this->log(self::WARNING, $message, $context);
	}

	public function notice($message, array $context = []): void
	{
		$this->log(self::NOTICE, $message, $context);
	}

	public function info($message, array $context = []): void
	{
		$this->log(self::INFO, $message, $context);
	}

	public function debug($message, array $context = []): void
	{
		$this->log(self::DEBUG, $message, $context);
	}

	public function success(string $message, array $context = [])
	{
		$this->log(self::SUCCESS, $message, $context);
	}

}