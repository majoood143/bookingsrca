<?php

namespace App\Support;

use App\Models\Translation;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Arr;
use Throwable;

/**
 * Wraps the file loader and layers the admin-saved translations on top, so a
 * database value wins and anything not overridden falls back to the lang files.
 */
class DatabaseTranslationLoader implements Loader
{
    protected ?array $overrides = null;

    public function __construct(protected Loader $files)
    {
    }

    public function load($locale, $group, $namespace = null)
    {
        $lines = $this->files->load($locale, $group, $namespace);

        // Package (namespaced) translations are not editable.
        if ($namespace !== null && $namespace !== '*') {
            return $lines;
        }

        foreach ($this->overrides()[$locale][$group] ?? [] as $key => $text) {
            // JSON keys are whole phrases, and a file may hold a literal dotted key.
            if ($group === '*' || array_key_exists($key, $lines)) {
                $lines[$key] = $text;
            } else {
                Arr::set($lines, $key, $text);
            }
        }

        return $lines;
    }

    public function flush(): void
    {
        $this->overrides = null;
    }

    protected function overrides(): array
    {
        if ($this->overrides !== null) {
            return $this->overrides;
        }

        try {
            return $this->overrides = Translation::overrides();
        } catch (Throwable) {
            // Table not migrated yet (fresh install, migrate:fresh): use the files only.
            return [];
        }
    }

    public function addNamespace($namespace, $hint)
    {
        $this->files->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->files->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->files->namespaces();
    }
}
