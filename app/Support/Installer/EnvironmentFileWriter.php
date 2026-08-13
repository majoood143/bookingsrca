<?php

namespace App\Support\Installer;

use Illuminate\Support\Facades\File;

class EnvironmentFileWriter
{
    protected string $path;

    protected array $lines;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? base_path('.env');

        if (! File::exists($this->path) && File::exists(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), $this->path);
        }

        $this->lines = [];

        if (File::exists($this->path)) {
            $contents = rtrim(preg_replace('/\r\n|\r/', "\n", File::get($this->path)), "\n");
            $this->lines = $contents === '' ? [] : explode("\n", $contents);
        }
    }

    public function get(string $key): ?string
    {
        foreach ($this->lines as $line) {
            if (preg_match('/^' . preg_quote($key, '/') . '=(.*)$/', $line, $matches)) {
                return $this->unquote($matches[1]);
            }
        }

        return null;
    }

    public function set(string $key, string $value): static
    {
        $formatted = $key . '=' . $this->format($value);
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/';
        $found = false;

        foreach ($this->lines as $i => $line) {
            if (preg_match($pattern, $line)) {
                $this->lines[$i] = $formatted;
                $found = true;
                break;
            }
        }

        if (! $found) {
            $this->lines[] = $formatted;
        }

        return $this;
    }

    public function save(): void
    {
        File::put($this->path, implode("\n", $this->lines));
    }

    protected function format(string $value): string
    {
        if ($value === '' || preg_match('/[\s"\'#$]/', $value)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }

        return $value;
    }

    protected function unquote(string $value): string
    {
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            return str_replace(['\\"', '\\\\'], ['"', '\\'], $matches[1]);
        }

        return $value;
    }
}
