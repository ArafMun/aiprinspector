<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class CommentService
{
    /**
     * @throws ConnectionException
     */
    public function post(string $repo, int $index, string $review, ?string $filePath = null, ?string $commitSha = null): bool
    {
        try {
            if (empty($review)) {
                Log::warning('Empty review provided, skipping comment', [
                    'action' => 'comment_empty_review',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'warning_type' => 'empty_content',
                ]);

                return false;
            }

            // Check if the review is an error response from AI provider
            if ($this->isErrorResponse($review)) {
                Log::warning('AI error response detected, skipping comment', [
                    'action' => 'comment_ai_error_response',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'error_response' => $review,
                    'warning_type' => 'ai_error',
                ]);

                return false;
            }

            // Try to parse and post line-specific comments
            $lineComments = $this->parseLineComments($review);

            if (! empty($lineComments) && $filePath) {
                $postedCount = 0;
                foreach ($lineComments as $lineComment) {
                    $success = app(GiteaService::class)->postDiffComment(
                        $repo,
                        $index,
                        $lineComment['body'],
                        $filePath,
                        $lineComment['line'],
                        $commitSha
                    );

                    if ($success) {
                        $postedCount++;
                    }
                }

                Log::info('Line-specific comments posted', [
                    'action' => 'line_comments_posted',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'file_path' => $filePath,
                    'total_comments' => count($lineComments),
                    'posted_comments' => $postedCount,
                ]);

                return $postedCount > 0;
            }

            // Fallback to general comment if no line comments or no file path
            $formattedReview = $this->formatReview($review);
            $success = app(GiteaService::class)->postComment($repo, $index, $formattedReview);

            if ($success) {
                Log::info('Review comment posted successfully', [
                    'action' => 'comment_posted_success',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'review_length' => strlen($formattedReview),
                ]);
            } else {
                Log::error('Failed to post review comment', [
                    'action' => 'comment_post_failed',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $index,
                    'error_type' => 'post_failure',
                ]);
            }

            return $success;

        } catch (ConnectionException $e) {
            Log::error('Connection error posting review', [
                'action' => 'comment_connection_error',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'error' => $e->getMessage(),
                'error_type' => 'connection_error',
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error posting review', [
                'action' => 'comment_post_error',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $index,
                'error' => $e->getMessage(),
                'error_type' => 'general_error',
            ]);

            return false;
        }
    }

    private function isErrorResponse(string $review): bool
    {
        // Common error patterns from AI providers
        $errorPatterns = [
            'Claude API error:',
            'OpenAI API error:',
            'Rate limit exceeded',
            'Unable to generate review',
            'API error',
            'connection error',
            'timeout',
            'failed to process',
            'error occurred',
            'service unavailable',
        ];

        // Check if review contains any error patterns
        foreach ($errorPatterns as $pattern) {
            if (stripos($review, $pattern) !== false) {
                return true;
            }
        }

        // Check if review is too short (likely an error message)
        if (strlen(trim($review)) < 50) {
            return true;
        }

        // Check if review looks like a generic error message
        $genericErrorIndicators = [
            'please try again',
            'unable to',
            'failed to',
            'error occurred',
            'something went wrong',
            'internal error',
        ];

        foreach ($genericErrorIndicators as $indicator) {
            if (stripos($review, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    private function formatReview(string $review): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');

        return <<<MARKDOWN
## 🤖 AI Code Review

*Generated on: {$timestamp}*

{$review}

---
*This review was generated by AI PR Inspector. Please review the suggestions carefully before applying them.*
MARKDOWN;
    }

    private function parseLineComments(string $review): array
    {
        $comments = [];
        $lines = explode("\n", $review);
        $currentLine = null;
        $currentIssueType = null;
        $currentProblem = null;
        $currentSuggestion = null;

        foreach ($lines as $line) {
            // Match "Line [LINE_NUMBER]: [ISSUE_TYPE]"
            if (preg_match('/^Line\s+(\d+):\s*(.+)$/i', trim($line), $matches)) {
                // Save previous comment if exists
                if ($currentLine !== null && $currentProblem !== null) {
                    $comments[] = [
                        'line' => (int) $currentLine,
                        'body' => $this->formatLineComment($currentIssueType, $currentProblem, $currentSuggestion),
                    ];
                }

                // Start new comment
                $currentLine = $matches[1];
                $currentIssueType = $matches[2];
                $currentProblem = null;
                $currentSuggestion = null;
            } elseif (preg_match('/^\*\*Problem:\*\*\s*(.+)$/i', trim($line), $matches)) {
                $currentProblem = $matches[1];
            } elseif (preg_match('/^\*\*Suggestion:\*\*\s*(.+)$/i', trim($line), $matches)) {
                $currentSuggestion = $matches[1];
            }
        }

        // Save last comment if exists
        if ($currentLine !== null && $currentProblem !== null) {
            $comments[] = [
                'line' => (int) $currentLine,
                'body' => $this->formatLineComment($currentIssueType, $currentProblem, $currentSuggestion),
            ];
        }

        return $comments;
    }

    private function formatLineComment(?string $issueType, string $problem, ?string $suggestion): string
    {
        $comment = "**{$issueType}:** {$problem}";

        if ($suggestion) {
            $comment .= "\n\n**Suggestion:** {$suggestion}";
        }

        return $comment;
    }
}
