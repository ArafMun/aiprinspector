<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GiteaService
{
    /**
     * @throws ConnectionException
     */
    public function getPullRequestFiles(string $repo, int $index): array
    {
        try {
            $baseUrl = config('services.gitea.base');
            $token = config('services.gitea.token');

            if (!$baseUrl || !$token) {
                throw new \InvalidArgumentException('Gitea base URL or token not configured');
            }

            $response = Http::withToken($token)
                ->timeout(30)
                ->get("$baseUrl/repos/$repo/pulls/$index/files");

            if (!$response->successful()) {
                Log::error('Failed to fetch PR files from Gitea', [
                    'action' => 'gitea_api_error',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'error_type' => 'api_error'
                ]);
                throw new \RuntimeException("Gitea API error: {$response->status()}");
            }

            $files = $response->json();

            Log::info('Successfully fetched PR files', [
                'action' => 'gitea_files_retrieved',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'file_count' => count($files)
            ]);

            return $files;

        } catch (ConnectionException $e) {
            Log::error('Connection error fetching PR files', [
                'action' => 'gitea_connection_error',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'error' => $e->getMessage(),
                'error_type' => 'connection_error'
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error fetching PR files', [
                'action' => 'gitea_files_fetch_error',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'error' => $e->getMessage(),
                'error_type' => 'general_error'
            ]);
            throw $e;
        }
    }

    /**
     * @throws ConnectionException
     */
    public function postComment(string $repo, int $index, string $body): bool
    {
        try {
            $baseUrl = config('services.gitea.base');
            $token = config('services.gitea.token');

            if (!$baseUrl || !$token) {
                throw new \InvalidArgumentException('Gitea base URL or token not configured');
            }

            if (empty($body)) {
                Log::warning('Attempted to post empty comment', [
                    'action' => 'gitea_empty_comment_attempt',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'warning_type' => 'empty_content'
                ]);
                return false;
            }

            $response = Http::withToken($token)
                ->timeout(30)
                ->post("$baseUrl/repos/$repo/issues/$index/comments", [
                    'body' => $body
                ]);

            if (!$response->successful()) {
                Log::error('Failed to post comment to Gitea', [
                    'action' => 'gitea_comment_post_error',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'error_type' => 'api_error'
                ]);
                return false;
            }

            Log::info('Successfully posted comment', [
                'action' => 'gitea_comment_posted',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'comment_length' => strlen($body)
            ]);

            return true;

        } catch (ConnectionException $e) {
            Log::error('Connection error posting comment', [
                'action' => 'gitea_comment_connection_error',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'error' => $e->getMessage(),
                'error_type' => 'connection_error'
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error posting comment', [
                'action' => 'gitea_comment_post_general_error',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'error' => $e->getMessage(),
                'error_type' => 'general_error'
            ]);
            return false;
        }
    }

    public function getPullRequestInfo(string $repo, int $index): array
    {
        try {
            $baseUrl = config('services.gitea.base');
            $token = config('services.gitea.token');

            $response = Http::withToken($token)
                ->timeout(30)
                ->get("$baseUrl/repos/$repo/pulls/$index");

            if (!$response->successful()) {
                Log::error('Failed to fetch PR info from Gitea', [
                    'action' => 'gitea_pr_info_error',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'status' => $response->status(),
                    'error_type' => 'api_error'
                ]);
                throw new \RuntimeException("Gitea API error: {$response->status()}");
            }

            return $response->json();

        } catch (ConnectionException $e) {
            Log::error('Connection error fetching PR info', [
                'action' => 'gitea_pr_info_connection_error',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'error' => $e->getMessage(),
                'error_type' => 'connection_error'
            ]);
            throw $e;
        }
    }
}
