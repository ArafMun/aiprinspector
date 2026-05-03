<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ChunkService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChunkServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChunkService $chunkService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chunkService = new ChunkService();
    }

    public function test_chunk_service_handles_empty_files_array(): void
    {
        $chunks = $this->chunkService->chunk([]);
        $this->assertIsArray($chunks);
        $this->assertEmpty($chunks);
    }

    public function test_chunk_service_processes_files_without_patch(): void
    {
        $files = [
            [
                'filename' => 'new_file.php',
                'status' => 'added',
                'patch' => ''
            ]
        ];

        $chunks = $this->chunkService->chunk($files);

        $this->assertCount(1, $chunks);
        $this->assertEquals('new_file.php', $chunks[0]['file']);
        $this->assertStringContainsString('added', $chunks[0]['patch']);
        $this->assertEquals('added', $chunks[0]['status']);
    }

    public function test_chunk_service_processes_files_with_patch(): void
    {
        $files = [
            [
                'filename' => 'existing_file.php',
                'patch' => '<?php echo "Hello"; ?>'
            ]
        ];

        $chunks = $this->chunkService->chunk($files);

        $this->assertCount(1, $chunks);
        $this->assertEquals('existing_file.php', $chunks[0]['file']);
        $this->assertEquals('<?php echo "Hello"; ?>', $chunks[0]['patch']);
    }

    public function test_chunk_service_skips_binary_files(): void
    {
        $files = [
            [
                'filename' => 'image.jpg',
                'patch' => 'binary content'
            ],
            [
                'filename' => 'document.pdf',
                'patch' => 'binary content'
            ]
        ];

        $chunks = $this->chunkService->chunk($files);

        $this->assertEmpty($chunks);
    }

    public function test_chunk_service_skips_large_files(): void
    {
        $files = [
            [
                'filename' => 'large_file.js',
                'patch' => str_repeat('content', 60000) // Very large file
            ]
        ];

        $chunks = $this->chunkService->chunk($files);

        $this->assertEmpty($chunks);
    }

    public function test_chunk_service_skips_lock_files(): void
    {
        $files = [
            [
                'filename' => 'composer.lock',
                'patch' => 'lock file content'
            ],
            [
                'filename' => 'package-lock.json',
                'patch' => 'lock file content'
            ],
            [
                'filename' => 'yarn.lock',
                'patch' => 'lock file content'
            ]
        ];

        $chunks = $this->chunkService->chunk($files);

        $this->assertEmpty($chunks);
    }

    public function test_chunk_service_skips_vendor_files(): void
    {
        $files = [
            [
                'filename' => 'vendor/some/package/file.php',
                'patch' => 'vendor content'
            ],
            [
                'filename' => 'node_modules/package/index.js',
                'patch' => 'node_modules content'
            ]
        ];

        $chunks = $this->chunkService->chunk($files);

        $this->assertEmpty($chunks);
    }

    public function test_chunk_service_splits_large_patches(): void
    {
        $largePatch = str_repeat("line of code\n", 300); // 300 lines
        $files = [
            [
                'filename' => 'large_patch.php',
                'patch' => $largePatch
            ]
        ];

        $chunks = $this->chunkService->chunk($files);

        $this->assertGreaterThan(1, count($chunks));

        // Verify chunks have proper structure
        foreach ($chunks as $chunk) {
            $this->assertArrayHasKey('file', $chunk);
            $this->assertArrayHasKey('patch', $chunk);
            $this->assertArrayHasKey('extension', $chunk);
            $this->assertArrayHasKey('size', $chunk);
            $this->assertEquals('large_patch.php', $chunk['file']);
        }
    }

    public function test_chunk_service_handles_mixed_file_types(): void
    {
        $files = [
            [
                'filename' => 'valid_file.php',
                'patch' => '<?php echo "test"; ?>'
            ],
            [
                'filename' => 'binary_file.jpg',
                'patch' => 'binary content'
            ],
            [
                'filename' => 'new_file.txt',
                'status' => 'added',
                'patch' => ''
            ]
        ];

        $chunks = $this->chunkService->chunk($files);

        // Should process valid_file.php and new_file.txt, but skip binary_file.jpg
        $this->assertCount(2, $chunks);

        $processedFiles = array_column($chunks, 'file');
        $this->assertContains('valid_file.php', $processedFiles);
        $this->assertContains('new_file.txt', $processedFiles);
        $this->assertNotContains('binary_file.jpg', $processedFiles);
    }
}
