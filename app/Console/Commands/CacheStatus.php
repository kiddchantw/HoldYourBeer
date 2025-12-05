<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheStatus extends Command
{
    protected $signature = 'cache:status 
                            {key? : Check specific cache key status}
                            {--list : List all known cache keys}';

    protected $description = 'Check cache status and information';

    /**
     * Known cache keys in the application
     */
    protected array $knownCacheKeys = [
        'brands_list' => [
            'description' => 'Brand list cache (sorted alphabetically)',
            'ttl' => 3600,
            'endpoint' => 'GET /api/v1/brands',
        ],
        'brand_statistics' => [
            'description' => 'Brand statistics cache (reserved)',
            'ttl' => null,
            'endpoint' => 'N/A (not implemented)',
        ],
        'beer_charts_data' => [
            'description' => 'Beer charts data cache (reserved)',
            'ttl' => null,
            'endpoint' => 'N/A (not implemented)',
        ],
    ];

    public function handle(): int
    {
        $key = $this->argument('key');

        if ($this->option('list')) {
            return $this->listCacheKeys();
        }

        if ($key) {
            return $this->checkSpecificKey($key);
        }

        return $this->showGeneralStatus();
    }

    protected function listCacheKeys(): int
    {
        $this->info('📋 Known Cache Keys:');
        $this->newLine();

        $headers = ['Key', 'Description', 'TTL', 'Status', 'Endpoint'];
        $rows = [];

        foreach ($this->knownCacheKeys as $cacheKey => $info) {
            $exists = Cache::has($cacheKey);
            $status = $exists ? '✅ Exists' : '❌ Not Found';
            $ttl = $info['ttl'] ? $info['ttl'].'s' : 'N/A';

            $rows[] = [
                $cacheKey,
                $info['description'],
                $ttl,
                $status,
                $info['endpoint'],
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('💡 Tip: Use "php artisan cache:status {key}" to check specific key details');

        return 0;
    }

    protected function checkSpecificKey(string $key): int
    {
        $this->info("🔍 Checking cache key: <comment>{$key}</comment>");
        $this->newLine();

        if (!isset($this->knownCacheKeys[$key])) {
            $this->warn("⚠️  Warning: This key is not in the known cache keys list.");
            $this->newLine();
        }

        $exists = Cache::has($key);

        if (!$exists) {
            $this->error("❌ Cache key '{$key}' does not exist.");
            return 1;
        }

        $value = Cache::get($key);
        $info = $this->knownCacheKeys[$key] ?? null;

        $this->info("✅ Cache key '{$key}' exists");
        $this->newLine();

        // Display information
        $this->line("<comment>Details:</comment>");
        if ($info) {
            $this->line("  Description: {$info['description']}");
            $this->line("  TTL: ".($info['ttl'] ? $info['ttl'].' seconds' : 'N/A'));
            $this->line("  Endpoint: {$info['endpoint']}");
        }

        $this->newLine();
        $this->line("<comment>Value Information:</comment>");
        $this->line("  Type: ".gettype($value));
        if (is_object($value)) {
            $this->line("  Class: ".get_class($value));
            if (method_exists($value, 'count')) {
                $this->line("  Count: ".$value->count());
            }
        } elseif (is_array($value)) {
            $this->line("  Count: ".count($value));
        }

        return 0;
    }

    protected function showGeneralStatus(): int
    {
        $this->info('📊 Cache Status Overview');
        $this->newLine();

        $driver = config('cache.default');
        $this->line("<comment>Cache Driver:</comment> {$driver}");

        $prefix = config('cache.prefix');
        if ($prefix) {
            $this->line("<comment>Cache Prefix:</comment> {$prefix}");
        }

        $this->newLine();
        $this->line("<comment>Known Cache Keys Status:</comment>");

        $headers = ['Key', 'Status'];
        $rows = [];

        foreach ($this->knownCacheKeys as $cacheKey => $info) {
            $exists = Cache::has($cacheKey);
            $status = $exists ? '✅ Exists' : '❌ Not Found';
            $rows[] = [$cacheKey, $status];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('💡 Use "php artisan cache:status --list" to see detailed information');
        $this->info('💡 Use "php artisan cache:status {key}" to check specific key');

        return 0;
    }
}

