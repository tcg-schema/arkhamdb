<?php

declare(strict_types=1);

namespace App\Mcp;

use Mcp\Capability\Attribute\McpTool;
use Symfony\Component\Yaml\Yaml;
use PDO;
use Exception;

class ArkhamTools
{
    private ?PDO $db = null;

    public function __construct()
    {
        $config = $this->loadConfig();
        $this->connectDb($config);
    }

    private function loadConfig(): array
    {
        $paramsFile = __DIR__ . '/../../app/config/parameters.yml';
        if (!file_exists($paramsFile)) {
            $paramsFile .= '.dist';
        }

        if (!file_exists($paramsFile)) {
            return [];
        }

        try {
            $yaml = Yaml::parseFile($paramsFile);
            return $yaml['parameters'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    private function connectDb(array $config)
    {
        $host = $config['database_host'] ?? '127.0.0.1';
        $port = $config['database_port'] ?? '3306';
        $name = $config['database_name'] ?? 'arkhamdb';
        $user = $config['database_user'] ?? 'root';
        $pass = $config['database_password'] ?? '';

        if (empty($port)) {
            $port = '3306';
        }

        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
        try {
            $this->db = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Exception $e) {
            $this->db = null;
        }
    }

    /**
     * Search for Arkham Horror LCG cards by name or traits.
     *
     * @param string $query The search query (name or traits)
     * @return string JSON encoded list of cards
     */
    #[McpTool]
    public function search_cards(string $query): string
    {
        if (!$this->db) {
            return "Database connection failed.";
        }
        $stmt = $this->db->prepare("SELECT code, name, real_text FROM card WHERE name LIKE ? OR real_traits LIKE ? LIMIT 10");
        $stmt->execute(["%$query%", "%$query%"]);
        $results = $stmt->fetchAll();
        return (string) json_encode($results, JSON_PRETTY_PRINT);
    }

    /**
     * Get full details of a card by its code.
     *
     * @param string $code The card code (e.g. 01001)
     * @return string JSON encoded card details
     */
    #[McpTool]
    public function get_card(string $code): string
    {
        if (!$this->db) {
            return "Database connection failed.";
        }
        $stmt = $this->db->prepare("SELECT * FROM card WHERE code = ?");
        $stmt->execute([$code]);
        $result = $stmt->fetch();
        if (!$result) {
            return "Card not found";
        }
        return (string) json_encode($result, JSON_PRETTY_PRINT);
    }
}
