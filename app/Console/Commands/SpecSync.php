<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class SpecSync extends Command
{
    protected $signature = 'spec:sync 
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Update files even if they have manual changes}';

    protected $description = 'Synchronize spec files with test files and update status tracking';

    protected array $updates = [];
    protected array $stats = [];

    public function handle(): int
    {
        $this->info('🔄 Synchronizing spec files with test files...');
        
        if ($this->option('dry-run')) {
            $this->warn('🏃 DRY RUN MODE - No files will be modified');
        }
        
        $this->syncFeatureFiles();
        $this->addMissingStatusTables();
        $this->generateReport();
        
        $this->info('✅ Spec sync completed.');
        return 0;
    }

    protected function syncFeatureFiles(): void
    {
        $featureFiles = $this->getFeatureFiles();
        $this->stats['total_features'] = count($featureFiles);
        $this->stats['updated_features'] = 0;
        $this->stats['missing_tests_found'] = 0;

        foreach ($featureFiles as $featureFile) {
            $this->syncFeatureFile($featureFile);
        }
    }

    protected function syncFeatureFile(string $featureFile): void
    {
        $content = File::get($featureFile);
        $relativePath = str_replace(base_path(), '', $featureFile);
        $originalContent = $content;
        
        // Extract or infer test file path
        $testPath = $this->getTestPath($content, $featureFile);
        
        if (!$testPath) {
            $this->updates[] = [
                'type' => 'no_test_path',
                'file' => $relativePath,
                'message' => 'Could not determine test file path'
            ];
            return;
        }

        // Update test path in feature file if needed
        $content = $this->updateTestPath($content, $testPath);
        
        // Update status tracking table
        if (File::exists(base_path($testPath))) {
            $content = $this->updateStatusTracking($content, $testPath, $relativePath);
        } else {
            $this->stats['missing_tests_found']++;
            $this->updates[] = [
                'type' => 'missing_test_file',
                'file' => $relativePath,
                'test_path' => $testPath,
                'message' => "Test file not found: {$testPath}"
            ];
        }

        // Save changes if content was modified
        if ($content !== $originalContent) {
            if (!$this->option('dry-run')) {
                File::put($featureFile, $content);
                $this->stats['updated_features']++;
            }
            
            $this->updates[] = [
                'type' => 'updated_feature',
                'file' => $relativePath,
                'message' => 'Updated status tracking table'
            ];
        }
    }

    protected function addMissingStatusTables(): void
    {
        $featureFiles = $this->getFeatureFiles();
        
        foreach ($featureFiles as $featureFile) {
            $content = File::get($featureFile);
            
            if (!$this->hasStatusTracking($content)) {
                $relativePath = str_replace(base_path(), '', $featureFile);
                $content = $this->addStatusTrackingTable($content, $relativePath);
                
                if (!$this->option('dry-run')) {
                    File::put($featureFile, $content);
                }
                
                $this->updates[] = [
                    'type' => 'added_status_table',
                    'file' => $relativePath,
                    'message' => 'Added missing status tracking table'
                ];
            }
        }
    }

    protected function getTestPath(string $content, string $featureFile): ?string
    {
        // Try to extract from existing content
        if (preg_match('/# 3\. Test:\s+(.+)/', $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Infer from feature file path
        return $this->inferTestPath($featureFile);
    }

    protected function inferTestPath(string $featureFile): ?string
    {
        $relativePath = str_replace(base_path('spec/features/'), '', $featureFile);
        $relativePath = str_replace('.feature', '', $relativePath);
        
        // Convert feature path to test path
        $parts = explode('/', $relativePath);
        $testName = '';
        
        foreach ($parts as $part) {
            $testName .= ucfirst(str_replace(['-', '_'], '', ucwords($part, '-_')));
        }
        
        $testName .= 'Test';
        
        // Try different test locations
        $possiblePaths = [
            "tests/Feature/{$testName}.php",
            "tests/Feature/" . end($parts) . "Test.php",
            "tests/Unit/{$testName}.php",
        ];
        
        foreach ($possiblePaths as $path) {
            if (File::exists(base_path($path))) {
                return $path;
            }
        }
        
        return "tests/Feature/{$testName}.php"; // Default suggestion
    }

    protected function updateTestPath(string $content, string $testPath): string
    {
        if (preg_match('/# 3\. Test:\s+(.+)/', $content)) {
            return preg_replace('/# 3\. Test:\s+(.+)/', "# 3. Test: {$testPath}", $content);
        }
        
        // If no test path exists, it will be added with the status table
        return $content;
    }

    protected function updateStatusTracking(string $content, string $testPath, string $featurePath): string
    {
        if (!File::exists(base_path($testPath))) {
            return $content;
        }
        
        $testContent = File::get(base_path($testPath));
        $testMethods = $this->extractTestMethods($testContent);
        
        if (empty($testMethods)) {
            return $content;
        }
        
        // Find existing status table or create new one
        if (preg_match('/# 4\. Scenario Status Tracking:(.*?)(?=\n\s*(?:Background|Scenario|Feature|\Z))/s', $content, $matches)) {
            $newTable = $this->generateStatusTable($testMethods, $featurePath);
            return str_replace($matches[0], $newTable, $content);
        } else {
            return $this->addStatusTrackingTable($content, $featurePath, $testMethods);
        }
    }

    protected function extractTestMethods(string $testContent): array
    {
        preg_match_all('/(?:public\s+)?function\s+(test_[a-zA-Z_]+)\s*\(/', $testContent, $matches);
        $methods = $matches[1] ?? [];
        
        // Also check for #[Test] attribute methods
        preg_match_all('/#\[Test\]\s*(?:public\s+)?function\s+([a-zA-Z_]+)\s*\(/', $testContent, $attrMatches);
        if (!empty($attrMatches[1])) {
            $methods = array_merge($methods, $attrMatches[1]);
        }
        
        return array_unique($methods);
    }

    protected function generateStatusTable(array $testMethods, string $featurePath): string
    {
        $table = "# 4. Scenario Status Tracking:\n";
        $table .= "# | Scenario Name                    | Status        | Test Method                    | UI  | Backend |\n";
        $table .= "# |----------------------------------|---------------|--------------------------------|-----|---------|\n";
        
        foreach ($testMethods as $method) {
            $scenarioName = $this->methodToScenarioName($method);
            $table .= "# | " . str_pad($scenarioName, 32) . " | DONE          | " . str_pad($method, 30) . " | DONE| DONE    |\n";
        }
        
        return $table;
    }

    protected function methodToScenarioName(string $method): string
    {
        $name = str_replace(['test_', '_'], ['', ' '], $method);
        return ucfirst(trim($name));
    }

    protected function addStatusTrackingTable(string $content, string $featurePath, array $testMethods = []): string
    {
        $testPath = $this->inferTestPath($featurePath);
        $designPath = str_replace(['spec/features/', '.feature'], ['docs/diagrams/', '-flow.md'], $featurePath);
        
        $statusBlock = "\n# 1. Status: TODO\n";
        $statusBlock .= "# 2. Design: {$designPath}\n";
        $statusBlock .= "# 3. Test: {$testPath}\n";
        
        if (!empty($testMethods)) {
            $statusBlock .= $this->generateStatusTable($testMethods, $featurePath);
        } else {
            $statusBlock .= "# 4. Scenario Status Tracking:\n";
            $statusBlock .= "# | Scenario Name                    | Status        | Test Method                    | UI  | Backend |\n";
            $statusBlock .= "# |----------------------------------|---------------|--------------------------------|-----|---------|\n";
            $statusBlock .= "# | TODO: Add scenarios              | TODO          | TODO: Add test methods         | TODO| TODO    |\n";
        }
        
        // Insert after the feature description, before scenarios
        if (preg_match('/^Feature:.*?(?=\n\s*(?:Background|Scenario))/ms', $content, $matches)) {
            return str_replace($matches[0], $matches[0] . $statusBlock, $content);
        }
        
        // If no scenarios found, add at the end of feature description
        $lines = explode("\n", $content);
        $insertIndex = 0;
        
        foreach ($lines as $index => $line) {
            if (strpos($line, 'Feature:') === 0) {
                // Find the end of feature description
                for ($i = $index + 1; $i < count($lines); $i++) {
                    if (trim($lines[$i]) === '' || strpos($lines[$i], '  ') === 0) {
                        continue;
                    }
                    $insertIndex = $i;
                    break;
                }
                break;
            }
        }
        
        if ($insertIndex > 0) {
            array_splice($lines, $insertIndex, 0, explode("\n", $statusBlock));
            return implode("\n", $lines);
        }
        
        return $content . $statusBlock;
    }

    protected function hasStatusTracking(string $content): bool
    {
        return strpos($content, '# 4. Scenario Status Tracking:') !== false;
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

    protected function generateReport(): void
    {
        $this->info("\n📊 Spec Sync Report:");
        
        // Statistics
        $this->table(['Metric', 'Count'], [
            ['Total Feature Files', $this->stats['total_features']],
            ['Updated Features', $this->stats['updated_features'] ?? 0],
            ['Missing Test Files Found', $this->stats['missing_tests_found'] ?? 0],
            ['Total Updates', count($this->updates)],
        ]);

        if (!empty($this->updates)) {
            $this->info("\n📝 Changes Made:");
            
            foreach ($this->updates as $update) {
                $icon = match($update['type']) {
                    'updated_feature' => '✏️',
                    'added_status_table' => '➕',
                    'missing_test_file' => '❌',
                    'no_test_path' => '❓',
                    default => '•'
                };
                
                $this->line("  {$icon} {$update['message']}");
                if (isset($update['file'])) {
                    $this->line("     File: {$update['file']}");
                }
                if (isset($update['test_path'])) {
                    $this->line("     Test: {$update['test_path']}");
                }
            }
        } else {
            $this->info("\n✅ All files are already in sync!");
        }

        if ($this->option('dry-run') && !empty($this->updates)) {
            $this->warn("\n🏃 DRY RUN: No files were actually modified. Run without --dry-run to apply changes.");
        }
    }
}