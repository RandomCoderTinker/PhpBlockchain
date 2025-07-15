<?php
/*
 * Copyright (c) 2025. ChainBase Project
 *  This file is part of ChainBase, a PHP-based EVM-compatible Layer 2 blockchain framework.
 *  Licensed under the MIT License. See the LICENSE file for details.
 */

namespace Chain\Config;

/**
 * Class Config
 *
 * Centralized configuration manager for the ChainBase blockchain framework.
 *
 * This class implements the Singleton pattern to ensure only one instance
 * of configuration data is loaded and shared throughout the application.
 *
 * Why use a singleton?
 * - Prevents repeated loading and parsing of configuration files.
 * - Ensures all parts of the system (miners, nodes, APIs, etc.) use the same
 *   config instance and state.
 * - Promotes consistency, especially for database credentials, RPC settings,
 *   or network constants.
 *
 * Instead of calling `new Config()` in every file, use `Config::getInstance()`
 * to access the shared configuration safely and efficiently.
 */
class Config
{

    private static ?Config $instance = null;
    private ?\PDO $pdo = null;
    private array $config;
    private string $configFile;

    /**
     * Config constructor.
     *
     * Loads configuration from a PHP file or generates it from `.env.example`.
     * @throws \Exception If the config file format is invalid
     */
    public function __construct()
    {
        $this->configFile = dirname(__DIR__, 2) . '/storage/const.config.php';

        if (!file_exists($this->configFile)) {
            $this->generateFromEnv();
        }

        $this->config = require $this->configFile;

        if (!is_array($this->config)) {
            throw new \Exception("Invalid config file format");
        }
    }

    /**
     * Get the singleton instance of the Config class.
     *
     * @return Config
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    /**
     * Generates a configuration file from `.env.example` with default values and secrets.
     *
     * @return array Summary of generated configuration data and file path
     * @throws \Exception If `.env.example` file is missing or file write fails
     */
    public function generateFromEnv(): array
    {
        $envPath = dirname(__DIR__, 2) . '/.env.example';

        if (!file_exists($envPath)) {
            throw new \Exception(".env.example not found at $envPath");
        }

        $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];

        foreach ($envLines as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $env[$key] = $value;
        }

        // Generate random application secrets
        $appKey = bin2hex(random_bytes(32));
        $jwtSecret = bin2hex(random_bytes(32));

        $this->config = [
            'app' => [
                'name' => $env['NETWORK_NAME'] ?? 'Chain Base',
                'version' => '1.0.0',
                'debug' => $env['DEBUG'] ?? false,
                'timezone' => $env['TIMEZONE'] ?? 'UTC',
                'installed' => true,
                'key' => $appKey,
                'installation_date' => date('Y-m-d H:i:s'),
                'script_root' => $env['SCRIPT_LOCATION'] ?? ''
            ],
            'network' => [
                'name' => $env['NETWORK_NAME'],
                'token_name' => $env['TOKEN_NAME'],
                'token_symbol' => $env['TOKEN_SYMBOL'],
                'token_decimals' => (int)($env['TOKEN_DECIMALS'] ?? 18),
                'token_total_supply' => $env['INITIAL_SUPPLY'] ?? 0,
                'chain_id' => (int)($env['CHAIN_ID'] ?? 1),
                'token_logo_uri' => $env['TOKEN_LOGO_URI'] ?? '',
                'token_website' => $env['TOKEN_WEBSITE'] ?? '',
                'token_description' => $env['TOKEN_DESCRIPTION'] ?? '',
                'token_explorer' => $env['TOKEN_EXPLORER'] ?? '',
                'block_time' => (int)($env['BLOCK_TIME'] ?? 10)
            ],
            'blockchain' => [
                'enable_binary_storage' => filter_var($env['BINARY_STORAGE'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'enable_encryption' => filter_var($env['BINARY_ENCRYPTED'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'encryption_key' => $env['BINARY_ENC_KEY'] ?? 'default_encryption_key_change_in_production',
                'data_dir' => $env['SCRIPT_LOCATION'] . $env['BINARY_LOCATION'] ?? 'storage/blockchain'
            ],
            'database' => [
                'host' => $env['DB_HOST'] ?? 'localhost',
                'port' => (int)($env['DB_PORT'] ?? 3306),
                'username' => $env['DB_USERNAME'] ?? 'root',
                'password' => $env['DB_PASSWORD'] ?? '',
                'database' => $env['DB_DATABASE'] ?? 'blockchain',
                'charset' => $env['DB_CHARSET'] ?? 'utf8mb4',
                'options' => []
            ],
            'node' => [
                'type' => $env['NODE_TYPE'] ?? 'full',
                'p2p_port' => (int)($env['P2P_PORT'] ?? 8545),
                'rpc_port' => (int)($env['RPC_PORT'] ?? 8546),
                'max_peers' => (int)($env['MAX_PEERS'] ?? 25),
                'bootstrap_nodes' => array_filter(explode(',', $env['BOOTSTRAP_NODES'] ?? '')),
                'user_agent' => $env['USER_AGENT'] ?? 'ChainBase/1.0'
            ],
            'security' => [
                'jwt_secret' => $jwtSecret,
                'session_lifetime' => 86400,
                'rate_limit' => [
                    'enabled' => true,
                    'max_requests' => 100,
                    'time_window' => 3600
                ]
            ],
            'logging' => [
                'level' => $env['LOG_LEVEL'] ?? 'info',
                'file' => $env['SCRIPT_LOCATION'] . $env['LOG_FILE'] ?? '/logs/app.log',
                'max_size' => '10MB',
                'max_files' => 5
            ]
        ];

        return $this->save();
    }


    /**
     * Saves the current configuration to file.
     *
     * @return array{
     *     message: string,
     *     path: string,
     *     data: array
     * }
     * @throws \Exception If file writing fails
     */
    public function save(): array
    {
        $content = "<?php\n\n// Auto-generated configuration file\n// Generated on: " . date('Y-m-d H:i:s') . "\n\nreturn " . var_export($this->config, true) . ";\n";

        if (file_put_contents($this->configFile, $content) === false) {
            throw new \Exception("Failed to write config file at $this->configFile");
        }

        return [
            'message' => 'Configuration saved successfully.',
            'path' => $this->configFile,
            'data' => $this->config
        ];
    }

    /**
     * Get a value from the configuration using dot notation.
     *
     * @param string $keyPath e.g., "database.host"
     * @return mixed|null The config value or null if not found
     */
    public function get(string $keyPath): mixed
    {
        $keys = explode('.', $keyPath);
        $value = $this->config;

        foreach ($keys as $key) {
            if (!isset($value[$key])) return null;
            $value = $value[$key];
        }

        return $value;
    }

    /**
     *  Returns a shared PDO connection.
     *  Opens the connection only once using lazy initialization.
     *
     * @return \PDO Active database connection
     * @throws \PDOException If connection fails
     */
    public function getDatabaseConnection(): \PDO
    {
        if (!$this->pdo) {
            $host = $this->get('database.host') ?? '';
            $port = $this->get('database.port') ?? 3306;
            $username = $this->get('database.username') ?? '';
            $password = $this->get('database.password') ?? '';
            $database = $this->get('database.database') ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

            $this->pdo = new \PDO($dsn, $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return $this->pdo;
    }
}