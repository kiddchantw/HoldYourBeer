<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Carbon\Carbon;

class SpecStatus extends Command
{
    protected $signature = 'spec:status 
                            {--dry-run : Show what would be updated without making changes}
                            {--format=markdown : Output format (markdown|json|table)}';

    protected $description = 'Generate and update features implementation status report';

    protected array $features = [];
    protected array $stats = [
        'total' => 0,
        'done' => 0,
        'in_progress' => 0,
        'todo' => 0
    ];

    public function handle(): int
    {
        $this->info('📊 Generating features status report...');
        
        if ($this->option('dry-run')) {
            $this->warn('🏃 DRY RUN MODE - Status file will not be updated');
        }
        
        $this->scanFeatureFiles();
        $this->calculateStats();
        
        match($this->option('format')) {
            'json' => $this->outputJson(),
            'table' => $this->outputTable(),
            default => $this->updateMarkdownFile()
        };
        
        $this->info('✅ Features status report completed.');
        return 0;
    }

    protected function scanFeatureFiles(): void
    {
        $featureFiles = $this->getFeatureFiles();
        
        foreach ($featureFiles as $featureFile) {
            $feature = $this->parseFeatureFile($featureFile);
            if ($feature) {
                $this->features[] = $feature;
            }
        }
        
        // Sort by status priority (DONE, IN_PROGRESS, TODO) then by name
        usort($this->features, function($a, $b) {
            $statusPriority = ['DONE' => 1, 'IN_PROGRESS' => 2, 'TODO' => 3];
            $aPriority = $statusPriority[$a['status']] ?? 4;
            $bPriority = $statusPriority[$b['status']] ?? 4;
            
            if ($aPriority === $bPriority) {
                return strcmp($a['name'], $b['name']);
            }
            
            return $aPriority <=> $bPriority;
        });
    }

    protected function parseFeatureFile(string $featureFile): ?array
    {
        $content = File::get($featureFile);
        $relativePath = str_replace(base_path('spec/features/'), '', $featureFile);
        
        // Extract feature name
        if (!preg_match('/^Feature:\s*(.+)$/m', $content, $matches)) {
            return null;
        }
        $featureName = trim($matches[1]);
        
        // Extract status
        $status = 'TODO'; // default
        if (preg_match('/# 1\. Status:\s*(\w+)/i', $content, $matches)) {
            $status = strtoupper(trim($matches[1]));
        }
        
        // Extract test file
        $testFile = '';
        if (preg_match('/# 3\. Test:\s*(.+)/', $content, $matches)) {
            $testFile = trim($matches[1]);
        }
        
        // Extract scenario statistics
        $scenarios = $this->extractScenarioStats($content);
        
        // Calculate completion percentage
        $completionPercentage = $this->calculateCompletionPercentage($content, $scenarios);
        
        // Extract pending tasks for IN_PROGRESS features
        $pendingTasks = [];
        if ($status === 'IN_PROGRESS') {
            $pendingTasks = $this->extractPendingTasks($content);
        }
        
        // Get last modification time
        $lastModified = Carbon::createFromTimestamp(filemtime($featureFile));
        
        return [
            'name' => $featureName,
            'path' => $relativePath,
            'status' => $status,
            'test_file' => $testFile,
            'completion_percentage' => $completionPercentage,
            'scenarios' => $scenarios,
            'pending_tasks' => $pendingTasks,
            'last_modified' => $lastModified,
            'file_path' => $featureFile
        ];
    }

    protected function extractScenarioStats(string $content): array
    {
        $stats = ['total' => 0, 'done' => 0, 'in_progress' => 0, 'todo' => 0];
        
        // Count scenarios in the tracking table
        if (preg_match_all('/\|\s*([^|]+)\s*\|\s*(DONE|IN_PROGRESS|TODO)\s*\|/', $content, $matches)) {
            $stats['total'] = count($matches[1]) - 1; // Exclude header row
            
            foreach ($matches[2] as $status) {
                $status = strtolower($status);
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
            }
        }
        
        return $stats;
    }

    protected function calculateCompletionPercentage(string $content, array $scenarios): int
    {
        if ($scenarios['total'] === 0) {
            // Fallback: count actual scenario blocks
            $scenarioCount = preg_match_all('/^\s*Scenario:/m', $content);
            if ($scenarioCount === 0) return 0;
            
            // Rough estimation based on overall status
            return match($this->extractStatus($content)) {
                'DONE' => 100,
                'IN_PROGRESS' => 50,
                'TODO' => 0,
                default => 0
            };
        }
        
        return $scenarios['total'] > 0 
            ? round(($scenarios['done'] / $scenarios['total']) * 100)
            : 0;
    }

    protected function extractPendingTasks(string $content): array
    {
        $tasks = [];
        
        // Extract from tracking table TODO items
        if (preg_match_all('/\|\s*([^|]+)\s*\|\s*TODO\s*\|.*\|\s*TODO\s*\|\s*TODO\s*\|/', $content, $matches)) {
            foreach ($matches[1] as $task) {
                $task = trim($task);
                if ($task !== 'Scenario Name') { // Skip header
                    $tasks[] = $task;
                }
            }
        }
        
        return $tasks;
    }

    protected function extractStatus(string $content): string
    {
        if (preg_match('/# 1\. Status:\s*(\w+)/i', $content, $matches)) {
            return strtoupper(trim($matches[1]));
        }
        return 'TODO';
    }

    protected function calculateStats(): void
    {
        $this->stats['total'] = count($this->features);
        
        foreach ($this->features as $feature) {
            $status = strtolower($feature['status']);
            if (isset($this->stats[$status])) {
                $this->stats[$status]++;
            }
        }
    }

    protected function updateMarkdownFile(): void
    {
        $statusFile = base_path('spec/features-status.md');
        $content = $this->generateMarkdownContent();
        
        if (!$this->option('dry-run')) {
            File::put($statusFile, $content);
            $this->info("📄 Updated status file: spec/features-status.md");
        } else {
            $this->info("📄 Would update: spec/features-status.md");
            $this->line($content);
        }
    }

    protected function generateMarkdownContent(): string
    {
        $now = Carbon::now();
        $donePercent = $this->stats['total'] > 0 ? round(($this->stats['done'] / $this->stats['total']) * 100, 1) : 0;
        $inProgressPercent = $this->stats['total'] > 0 ? round(($this->stats['in_progress'] / $this->stats['total']) * 100, 1) : 0;
        $todoPercent = $this->stats['total'] > 0 ? round(($this->stats['todo'] / $this->stats['total']) * 100, 1) : 0;
        
        $content = "# Features Implementation Status\n\n";
        $content .= "> 自動生成時間：{$now->format('Y-m-d H:i:s')}\n";
        $content .= "> 總計：{$this->stats['total']} 個功能規格\n\n";
        
        $content .= "## 📊 概覽統計\n\n";
        $content .= "- ✅ **已完成**：{$this->stats['done']} 個功能 ({$donePercent}%)\n";
        $content .= "- 🚧 **進行中**：{$this->stats['in_progress']} 個功能 ({$inProgressPercent}%)\n";
        $content .= "- ❌ **未開始**：{$this->stats['todo']} 個功能 ({$todoPercent}%)\n\n";
        
        $content .= "## 🎯 詳細狀態\n\n";
        
        // Group by status
        $grouped = ['DONE' => [], 'IN_PROGRESS' => [], 'TODO' => []];
        foreach ($this->features as $feature) {
            $grouped[$feature['status']][] = $feature;
        }
        
        foreach ($grouped as $status => $features) {
            if (empty($features)) continue;
            
            $icon = match($status) {
                'DONE' => '✅',
                'IN_PROGRESS' => '🚧',
                'TODO' => '❌',
                default => '•'
            };
            
            $statusName = match($status) {
                'DONE' => '已完成功能',
                'IN_PROGRESS' => '進行中功能',
                'TODO' => '未開始功能',
                default => $status
            };
            
            $content .= "### $icon $statusName ($status)\n\n";
            
            if ($status === 'DONE') {
                $content .= "| 功能名稱 | 路徑 | 測試檔案 | 最後更新 |\n";
                $content .= "|---------|------|---------|----------|\n";
                foreach ($features as $feature) {
                    $testFile = $feature['test_file'] ? basename($feature['test_file']) : '-';
                    $lastModified = $feature['last_modified']->format('Y-m-d');
                    $content .= "| {$feature['name']} | `{$feature['path']}` | `{$testFile}` | {$lastModified} |\n";
                }
            } elseif ($status === 'IN_PROGRESS') {
                $content .= "| 功能名稱 | 路徑 | 完成度 | 待辦項目 | 備註 |\n";
                $content .= "|---------|------|-------|---------|------|\n";
                foreach ($features as $feature) {
                    $pendingTasks = !empty($feature['pending_tasks']) 
                        ? implode('、', array_slice($feature['pending_tasks'], 0, 3))
                        : '-';
                    if (count($feature['pending_tasks']) > 3) {
                        $pendingTasks .= '...';
                    }
                    $content .= "| {$feature['name']} | `{$feature['path']}` | {$feature['completion_percentage']}% | {$pendingTasks} | - |\n";
                }
            } else { // TODO
                $content .= "| 功能名稱 | 路徑 | 優先級 | 預估工時 | 依賴項目 |\n";
                $content .= "|---------|------|-------|---------|----------|\n";
                foreach ($features as $feature) {
                    $content .= "| {$feature['name']} | `{$feature['path']}` | Medium | - | - |\n";
                }
            }
            
            $content .= "\n";
        }
        
        $content .= "## 🔄 更新機制\n\n";
        $content .= "### 自動更新命令\n";
        $content .= "```bash\n";
        $content .= "# 掃描並更新狀態文件\n";
        $content .= "php artisan spec:status\n\n";
        $content .= "# 僅顯示狀態，不更新文件\n";
        $content .= "php artisan spec:status --dry-run\n\n";
        $content .= "# 以表格格式顯示\n";
        $content .= "php artisan spec:status --format=table\n\n";
        $content .= "# 輸出 JSON 格式\n";
        $content .= "php artisan spec:status --format=json\n";
        $content .= "```\n\n";
        
        $content .= "### 手動更新流程\n";
        $content .= "1. 完成 feature 開發和測試\n";
        $content .= "2. 更新 `.feature` 檔案中的狀態標記：`# 1. Status: DONE`\n";
        $content .= "3. 執行 `php artisan spec:status` 更新此文件\n";
        $content .= "4. 提交變更到版本控制\n\n";
        
        $content .= "### Claude Code 更新協議\n";
        $content .= "當完成任何 feature 開發時，Claude Code 將自動：\n";
        $content .= "1. 更新相應 `.feature` 檔案的狀態標記\n";
        $content .= "2. 執行 `php artisan spec:status` 更新狀態\n";
        $content .= "3. 在 commit 訊息中標記狀態變更\n\n";
        
        $content .= "## 📋 狀態標記規範\n\n";
        $content .= "在 `.feature` 檔案中使用以下標準格式：\n\n";
        $content .= "```\n";
        $content .= "# 1. Status: DONE|IN_PROGRESS|TODO\n";
        $content .= "# 2. Design: docs/diagrams/feature-name-flow.md\n";
        $content .= "# 3. Test: tests/Feature/FeatureNameTest.php\n";
        $content .= "# 4. Scenario Status Tracking:\n";
        $content .= "# | Scenario Name | Status | Test Method | UI | Backend |\n";
        $content .= "```\n\n";
        
        $content .= "---\n\n";
        $content .= "*此文件由 `php artisan spec:status` 命令自動維護*  \n";
        $content .= "*上次掃描：{$now->format('Y-m-d H:i:s')}*\n";
        
        return $content;
    }

    protected function outputTable(): void
    {
        $headers = ['Feature', 'Status', 'Path', 'Completion', 'Test File'];
        $rows = [];
        
        foreach ($this->features as $feature) {
            $status = match($feature['status']) {
                'DONE' => '✅ DONE',
                'IN_PROGRESS' => '🚧 IN_PROGRESS',
                'TODO' => '❌ TODO',
                default => $feature['status']
            };
            
            $rows[] = [
                $feature['name'],
                $status,
                $feature['path'],
                $feature['completion_percentage'] . '%',
                basename($feature['test_file'] ?: '-')
            ];
        }
        
        $this->table($headers, $rows);
        
        // Summary
        $this->info("\n📊 Summary:");
        $this->table(['Status', 'Count', 'Percentage'], [
            ['✅ Done', $this->stats['done'], round(($this->stats['done'] / $this->stats['total']) * 100, 1) . '%'],
            ['🚧 In Progress', $this->stats['in_progress'], round(($this->stats['in_progress'] / $this->stats['total']) * 100, 1) . '%'],
            ['❌ TODO', $this->stats['todo'], round(($this->stats['todo'] / $this->stats['total']) * 100, 1) . '%'],
            ['📊 Total', $this->stats['total'], '100%'],
        ]);
    }

    protected function outputJson(): void
    {
        $data = [
            'generated_at' => Carbon::now()->toISOString(),
            'summary' => $this->stats,
            'features' => array_map(function($feature) {
                return [
                    'name' => $feature['name'],
                    'path' => $feature['path'],
                    'status' => $feature['status'],
                    'completion_percentage' => $feature['completion_percentage'],
                    'test_file' => $feature['test_file'],
                    'scenarios' => $feature['scenarios'],
                    'pending_tasks' => $feature['pending_tasks'],
                    'last_modified' => $feature['last_modified']->toISOString(),
                ];
            }, $this->features)
        ];
        
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function getFeatureFiles(): array
    {
        $finder = new Finder();
        $files = [];
        
        if (File::exists(base_path('spec/features'))) {
            $finder->files()->in(base_path('spec/features'))->name('*.feature');
            foreach ($finder as $file) {
                $files[] = $file->getRealPath();
            }
        }
        
        return $files;
    }
}