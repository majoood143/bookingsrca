<?php

namespace App\Support\Installer;

use PDO;
use PDOException;

class DatabaseConnectionTester
{
    /**
     * Test the given MySQL credentials, creating the database if the
     * connection succeeds but the named database doesn't exist yet.
     *
     * @return array{success: bool, message: ?string, created: bool}
     */
    public function test(string $host, string $port, string $database, string $username, string $password): array
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            return [
                'success' => false,
                'message' => __('installer.database.invalid_name'),
                'created' => false,
            ];
        }

        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $exists = $pdo->query(
                'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ' . $pdo->quote($database)
            )->fetchColumn();

            $created = false;

            if (! $exists) {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $created = true;
            }

            return ['success' => true, 'message' => null, 'created' => $created];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => $this->humanize($e),
                'created' => false,
            ];
        }
    }

    protected function humanize(PDOException $e): string
    {
        return match (true) {
            str_contains($e->getMessage(), 'Access denied') => __('installer.database.access_denied'),
            str_contains($e->getMessage(), 'known') || str_contains($e->getMessage(), 'timed out') => __('installer.database.unknown_host'),
            default => __('installer.database.connection_failed', ['error' => $e->getMessage()]),
        };
    }
}
