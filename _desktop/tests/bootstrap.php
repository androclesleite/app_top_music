<?php

/**
 * Bootstrap file for tests
 * Provides additional setup and configuration for testing environment
 */

use Illuminate\Contracts\Console\Kernel;

require_once __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = require __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Bootstrap The Application
|--------------------------------------------------------------------------
|
| Before we can make requests to our application, we need to bootstrap it
| so we can create an application instance which will handle the requests.
|
*/

$app->make(Kernel::class)->bootstrap();

/*
|--------------------------------------------------------------------------
| Testing Environment Variables
|--------------------------------------------------------------------------
|
| Set additional environment variables specifically for testing that may
| not be appropriate to set in phpunit.xml
|
*/

// Disable broadcasting during tests
config(['broadcasting.default' => 'log']);

// Use array cache driver for faster tests
config(['cache.default' => 'array']);

// Use sync queue driver for immediate processing
config(['queue.default' => 'sync']);

// Disable logging during tests to reduce I/O
config(['logging.default' => 'single']);
config(['logging.channels.single.level' => 'critical']);

// Set fast hashing for tests
config(['hashing.bcrypt.rounds' => 4]);

/*
|--------------------------------------------------------------------------
| Testing Utilities
|--------------------------------------------------------------------------
|
| Global helper functions and utilities that can be used across all tests
|
*/

/**
 * Create a temporary file with content for testing
 */
function createTempFile(string $content = '', string $extension = 'txt'): string
{
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.' . $extension;
    file_put_contents($tmpFile, $content);
    return $tmpFile;
}

/**
 * Clean up temporary files created during tests
 */
function cleanupTempFiles(): void
{
    $pattern = sys_get_temp_dir() . '/test_*';
    $files = glob($pattern);
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

/**
 * Generate a valid YouTube URL for testing
 */
function generateTestYouTubeUrl(): string
{
    $videoId = str_replace(['+', '/', '='], ['a', 'b', 'c'], base64_encode(random_bytes(8)));
    $videoId = substr($videoId, 0, 11);
    return "https://www.youtube.com/watch?v={$videoId}";
}

/**
 * Generate multiple test YouTube URLs
 */
function generateTestYouTubeUrls(int $count = 1): array
{
    $urls = [];
    for ($i = 0; $i < $count; $i++) {
        $urls[] = generateTestYouTubeUrl();
    }
    return $urls;
}

/**
 * Create test data for songs
 */
function createTestSongData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Test Song ' . uniqid(),
        'youtube_url' => generateTestYouTubeUrl(),
        'position' => null,
        'plays_count' => rand(0, 1000),
    ], $overrides);
}

/**
 * Create test data for suggestions
 */
function createTestSuggestionData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Test Suggestion ' . uniqid(),
        'youtube_url' => generateTestYouTubeUrl(),
        'suggested_by' => 'Test User ' . uniqid(),
        'status' => 'pending',
    ], $overrides);
}

/*
|--------------------------------------------------------------------------
| Test Database Utilities
|--------------------------------------------------------------------------
|
| Utilities for managing test database state and setup
|
*/

/**
 * Check if testing database is properly configured
 */
function checkTestingDatabase(): bool
{
    try {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        
        if ($connection === 'sqlite' && $database === ':memory:') {
            return true;
        }
        
        if ($connection === 'mysql' && str_contains($database, 'test')) {
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Verify test environment setup
 */
function verifyTestEnvironment(): void
{
    // Ensure we're in testing environment
    if (app()->environment() !== 'testing') {
        throw new Exception('Tests must be run in testing environment');
    }
    
    // Verify database configuration
    if (!checkTestingDatabase()) {
        echo "Warning: Testing database may not be properly configured.\n";
        echo "Current connection: " . config('database.default') . "\n";
        echo "Current database: " . config('database.connections.' . config('database.default') . '.database') . "\n";
    }
}

// Run environment verification
verifyTestEnvironment();

/*
|--------------------------------------------------------------------------
| Cleanup Hooks
|--------------------------------------------------------------------------
|
| Register cleanup functions to run after tests complete
|
*/

register_shutdown_function('cleanupTempFiles');