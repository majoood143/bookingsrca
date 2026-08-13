<?php

namespace App\Support\Installer;

class RequirementsChecker
{
    protected const MIN_PHP_VERSION = '8.2.0';

    protected const REQUIRED_EXTENSIONS = [
        'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'gd', 'hash',
        'mbstring', 'openssl', 'pcre', 'pdo', 'pdo_mysql', 'session',
        'tokenizer', 'xml', 'zip',
    ];

    protected const WRITABLE_PATHS = [
        'storage',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ];

    /**
     * @return array{php: array, extensions: array, writable: array, envWritable: bool, passed: bool}
     */
    public function check(): array
    {
        $php = [
            'required' => self::MIN_PHP_VERSION,
            'current' => PHP_VERSION,
            'passed' => version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>='),
        ];

        $extensions = collect(self::REQUIRED_EXTENSIONS)
            ->mapWithKeys(fn (string $ext) => [$ext => extension_loaded($ext)])
            ->all();

        $writable = collect(self::WRITABLE_PATHS)
            ->mapWithKeys(fn (string $path) => [$path => is_writable(base_path($path))])
            ->all();

        $envWritable = file_exists(base_path('.env'))
            ? is_writable(base_path('.env'))
            : is_writable(base_path());

        $passed = $php['passed']
            && ! in_array(false, $extensions, true)
            && ! in_array(false, $writable, true)
            && $envWritable;

        return compact('php', 'extensions', 'writable', 'envWritable', 'passed');
    }
}
