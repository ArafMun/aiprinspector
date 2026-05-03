<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ChunkService
{
    private const MAX_PATCH_SIZE = 15000; // Maximum characters per chunk
    private const MAX_LINES_PER_CHUNK = 200; // Maximum lines per chunk

    public function chunk(array $files): array
    {
        $chunks = [];
        $totalFiles = count($files);
        $processedFiles = 0;

        Log::info('Starting chunking process', [
            'action' => 'chunking_started',
            'business_context' => 'ai_code_review',
            'total_files' => $totalFiles
        ]);

        foreach ($files as $file) {
            $processedFiles++;

            if (empty($file['patch'])) {
                // Handle files without patch (added, deleted, renamed files)
                $filename = $file['filename'] ?? 'unknown';
                $status = $file['status'] ?? 'unknown';

                Log::info('Processing file without patch', [
                    'action' => 'chunking_file_no_patch',
                    'business_context' => 'ai_code_review',
                    'file' => $filename,
                    'status' => $status,
                    'progress' => "$processedFiles/$totalFiles"
                ]);

                // Create a minimal chunk for files without patch
                $chunks[] = [
                    'file' => $filename,
                    'patch' => "File {$status}: {$filename}",
                    'extension' => pathinfo($filename, PATHINFO_EXTENSION),
                    'size' => strlen("File {$status}: {$filename}"),
                    'status' => $status
                ];
                continue;
            }

            $filename = $file['filename'];
            $patch = $file['patch'];
            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);

            // Filter out binary files and large generated files
            if ($this->shouldSkipFile($filename, $fileExtension, $patch)) {
                Log::info('Skipping file due to type or size', [
                    'action' => 'chunking_file_skipped',
                    'business_context' => 'ai_code_review',
                    'file' => $filename,
                    'extension' => $fileExtension,
                    'patch_size' => strlen($patch),
                    'progress' => "$processedFiles/$totalFiles"
                ]);
                continue;
            }

            // Check if patch needs to be split into multiple chunks
            if ($this->needsChunking($patch)) {
                $fileChunks = $this->splitPatch($filename, $patch);
                $chunks = array_merge($chunks, $fileChunks);

                Log::info('Split large file into chunks', [
                    'action' => 'chunking_file_split',
                    'business_context' => 'ai_code_review',
                    'file' => $filename,
                    'original_size' => strlen($patch),
                    'chunks_created' => count($fileChunks),
                    'progress' => "$processedFiles/$totalFiles"
                ]);
            } else {
                $chunks[] = [
                    'file' => $filename,
                    'patch' => $patch,
                    'extension' => $fileExtension,
                    'size' => strlen($patch)
                ];

                Log::info('Added file as single chunk', [
                    'action' => 'chunking_file_single',
                    'business_context' => 'ai_code_review',
                    'file' => $filename,
                    'size' => strlen($patch),
                    'progress' => "$processedFiles/$totalFiles"
                ]);
            }
        }

        Log::info('Chunking completed', [
            'action' => 'chunking_completed',
            'business_context' => 'ai_code_review',
            'total_chunks' => count($chunks),
            'total_files_processed' => $processedFiles,
            'files_skipped' => $totalFiles - $processedFiles
        ]);

        return $chunks;
    }

    private function shouldSkipFile(string $filename, string $extension, string $patch): bool
    {
        // Skip binary files
        $binaryExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'ico', 'pdf', 'zip', 'tar', 'gz', 'exe', 'dll', 'so'];
        if (in_array(strtolower($extension), $binaryExtensions)) {
            return true;
        }

        // Skip very large files (likely generated or minified)
        if (strlen($patch) > 50000) {
            return true;
        }

        // Skip lock files and dependency files
        $skipPatterns = [
            '/composer\.lock$/',
            '/package-lock\.json$/',
            '/yarn\.lock$/',
            '/\.min\.(js|css)$/',
            '/vendor\/.*$/',
            '/node_modules\/.*$/',
            '/\.git\/.*$/'
        ];

        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }

    private function needsChunking(string $patch): bool
    {
        $lineCount = substr_count($patch, "\n") + 1;
        $size = strlen($patch);

        return $size > self::MAX_PATCH_SIZE || $lineCount > self::MAX_LINES_PER_CHUNK;
    }

    private function splitPatch(string $filename, string $patch): array
    {
        $chunks = [];
        $lines = explode("\n", $patch);
        $totalLines = count($lines);

        $chunkSize = min(self::MAX_LINES_PER_CHUNK, max(50, intval($totalLines / 3)));
        $currentChunkLines = [];
        $currentChunkSize = 0;
        $chunkNumber = 1;

        for ($i = 0; $i < $totalLines; $i++) {
            $line = $lines[$i];
            $lineLength = strlen($line) + 1; // +1 for newline

            // Check if adding this line would exceed limits
            if ($currentChunkSize + $lineLength > self::MAX_PATCH_SIZE ||
                count($currentChunkLines) >= $chunkSize) {

                // Save current chunk if not empty
                if (!empty($currentChunkLines)) {
                    $chunks[] = [
                        'file' => $filename,
                        'patch' => implode("\n", $currentChunkLines),
                        'extension' => pathinfo($filename, PATHINFO_EXTENSION),
                        'size' => $currentChunkSize,
                        'chunk_number' => $chunkNumber,
                        'total_chunks' => 'pending' // Will be updated later
                    ];
                    $chunkNumber++;
                }

                // Start new chunk
                $currentChunkLines = [$line];
                $currentChunkSize = $lineLength;
            } else {
                $currentChunkLines[] = $line;
                $currentChunkSize += $lineLength;
            }
        }

        // Add remaining lines as last chunk
        if (!empty($currentChunkLines)) {
            $chunks[] = [
                'file' => $filename,
                'patch' => implode("\n", $currentChunkLines),
                'extension' => pathinfo($filename, PATHINFO_EXTENSION),
                'size' => $currentChunkSize,
                'chunk_number' => $chunkNumber,
                'total_chunks' => $chunkNumber
            ];
        }

        // Update total_chunks for all chunks
        $totalChunks = count($chunks);
        foreach ($chunks as &$chunk) {
            $chunk['total_chunks'] = $totalChunks;
        }

        return $chunks;
    }
}
