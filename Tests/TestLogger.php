<?php

/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

declare(strict_types=1);

use Chain\Utils\Logger;

require dirname(__DIR__) . "/vendor/autoload.php";

// Test Logger file
Logger::getInstance()->error("This is an error");
Logger::getInstance()->warning("This is an warning");
Logger::getInstance()->info("This is an info");
Logger::getInstance()->debug("This is a debug");
Logger::getInstance()->success("This is a success");
