<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class SpecCheck extends Command
{
    protected $signature = 'spec:check 
                            {--strict : Exit with error code if inconsistencies found}
                            {--ci : Format output for CI/CD environment}';

    protected $description = 'Check consistency between spec files and test files';

    protected array $inconsistencies = [];
    protected array $stats = [];

    public function handle(): int
    {
        $this->info('🔍 Checking spec-test consistency...');
        
        $this->checkFeatureFiles();
        $this->checkTestFiles();
        $this->generateReport();
        
        if ($this->option('strict') && !empty($this->inconsistencies)) {
            $this->error('❌ Inconsistencies found. Run with --help for details.');
            return 1;
        }
        
        $this->info('✅ Spec check completed.');
        return 0;
    }

    protected function checkFeatureFiles(): void
    {
        $featureFiles = $this->getFeatureFiles();
        $this->stats['total_features'] = count($featureFiles);
        $this->stats['features_with_tracking'] = 0;
        $this->stats['features_missing_tests'] = 0;

        foreach ($featureFiles as $featureFile) {
            $this->checkFeatureFile($featureFile);
        }
    }

    protected function checkFeatureFile(string $featureFile): void
    {
        $content = File::get($featureFile);
        $relativePath = str_replace(base_path(), '', $featureFile);
        
        // Check if feature has status tracking
        if ($this->hasStatusTracking($content)) {
            $this->stats['features_with_tracking']++;
            
            // Extract test file path from feature
            $testPath = $this->extractTestPath($content);
            if ($testPath && !File::exists(base_path($testPath))) {
                $this->inconsistencies[] = [
                    'type' => 'missing_test_file',
                    'feature' => $relativePath,
                    'expected_test' => $testPath,
                    'message' => "Test file not found: {$testPath}"
                ];
                $this->stats['features_missing_tests']++;
            }
            
            // Check test methods mentioned in tracking table
            $this->checkTrackingTableMethods($content, $relativePath, $testPath);
        } else {
            $this->inconsistencies[] = [
                'type' => 'missing_status_tracking',
                'feature' => $relativePath,
                'message' => 'Feature file missing status tracking table'
            ];
        }
    }

    protected function checkTestFiles(): void
    {
        $testFiles = $this->getTestFiles();
        $this->stats['total_tests'] = count($testFiles);
        $this->stats['tests_with_spec_annotation'] = 0;

        foreach ($testFiles as $testFile) {
            $content = File::get($testFile);
            if ($this->hasSpecAnnotation($content)) {
                $this->stats['tests_with_spec_annotation']++;
            } else {
                $relativePath = str_replace(base_path(), '', $testFile);
                $this->inconsistencies[] = [
                    'type' => 'missing_spec_annotation',
                    'test' => $relativePath,
                    'message' => 'Test file missing @covers spec annotation'
                ];
            }
        }
    }

    protected function generateReport(): void
    {
        if ($this->option('ci')) {
            $this->generateCIReport();
        } else {
            $this->generateHumanReport();
        }
    }

    protected function generateHumanReport(): void
    {
        $this->info("\n📊 Spec-Test Consistency Report:");
        
        // Statistics
        $this->table(['Metric', 'Count'], [
            ['Total Feature Files', $this->stats['total_features']],
            ['Features with Status Tracking', $this->stats['features_with_tracking']],
            ['Features Missing Tests', $this->stats['features_missing_tests']],
            ['Total Test Files', $this->stats['total_tests']],
            ['Tests with Spec Annotations', $this->stats['tests_with_spec_annotation']],
        ]);

        // Issues
        if (!empty($this->inconsistencies)) {
            $this->error("\n❌ Found " . count($this->inconsistencies) . " inconsistencies:");
            
            foreach ($this->inconsistencies as $issue) {
                $this->line("  • [{$issue['type']}] {$issue['message']}");
                if (isset($issue['feature'])) {
                    $this->line("    Feature: {$issue['feature']}");
                }
                if (isset($issue['test'])) {
                    $this->line("    Test: {$issue['test']}");
                }
                $this->line("");
            }

            $this->info("\n💡 Suggestions:");
            $this->line("  • Run 'php artisan spec:sync' to auto-update status tracking");
            $this->line("  • Add missing @covers annotations to test files");
            $this->line("  • Create missing test files referenced in feature files");
        } else {
            $this->info("\n✅ No inconsistencies found!");
        }
    }

    protected function generateCIReport(): void
    {
        $report = [
            'status' => empty($this->inconsistencies) ? 'PASS' : 'FAIL',
            'stats' => $this->stats,
            'issues' => $this->inconsistencies
        ];
        
        $this->line(json_encode($report, JSON_PRETTY_PRINT));
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

    protected function getTestFiles(): array
    {
        $finder = new Finder();
        $files = [];
        
        if (File::exists(base_path('tests'))) {
            $finder->files()->in(base_path('tests'))->name('*Test.php');
            foreach ($finder as $file) {
                $files[] = $file->getRealPath();
            }
        }
        
        return $files;
    }

    protected function hasStatusTracking(string $content): bool
    {
        return strpos($content, '# 4. Scenario Status Tracking:') !== false;
    }

    protected function hasSpecAnnotation(string $content): bool
    {
        return preg_match('/@covers\s+\\\\spec\\\\features/', $content);
    }

    protected function extractTestPath(string $content): ?string
    {
        if (preg_match('/# 3\. Test:\s+(.+)/', $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    protected function checkTrackingTableMethods(string $content, string $featureFile, ?string $testPath): void
    {
        if (!$testPath || !File::exists(base_path($testPath))) {
            return;
        }

        // Extract method names from tracking table
        preg_match_all('/\|\s*[^|]+\s*\|\s*[^|]+\s*\|\s*([^|]+)\s*\|\s*[^|]+\s*\|\s*[^|]+\s*\|/', $content, $matches);
        
        if (empty($matches[1])) {
            return;
        }

        $testContent = File::get(base_path($testPath));
        
        foreach ($matches[1] as $methodName) {
            $methodName = trim($methodName);
            if ($methodName === 'Test Method') continue; // Skip header
            
            if (!preg_match("/function\s+{$methodName}\s*\(/", $testContent)) {
                $this->inconsistencies[] = [
                    'type' => 'missing_test_method',
                    'feature' => $featureFile,
                    'test' => $testPath,
                    'method' => $methodName,
                    'message' => "Test method '{$methodName}' not found in test file"
                ];
            }
        }
    }
}