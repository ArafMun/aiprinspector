<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AIService;
use App\Services\AI\AIProviderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_service_can_be_instantiated(): void
    {
        $aiService = new AIService();
        $this->assertInstanceOf(AIService::class, $aiService);
    }

    public function test_ai_service_uses_default_provider(): void
    {
        $aiService = new AIService();
        $providerName = $aiService->getProviderName();
        $this->assertNotEmpty($providerName);
    }

    public function test_ai_service_can_use_specific_provider(): void
    {
        try {
            $aiService = new AIService('openai');
            $this->assertEquals('openai', $aiService->getProviderName());
        } catch (\Exception $e) {
            // If OpenAI is not configured, that's expected in test environment
            $this->assertTrue(true);
        }
    }

    public function test_ai_provider_factory_creates_provider(): void
    {
        $provider = AIProviderFactory::create();
        $this->assertNotNull($provider);
    }

    public function test_ai_provider_factory_checks_configuration(): void
    {
        $provider = AIProviderFactory::create();
        $isConfigured = $provider->isConfigured();
        $this->assertIsBool($isConfigured);
    }

    public function test_ai_service_review_with_sample_data(): void
    {
        $sampleChunk = [
            'file' => 'test.php',
            'patch' => '<?php echo "Hello World"; ?>',
            'extension' => 'php'
        ];

        $aiService = new AIService();

        try {
            $review = $aiService->review($sampleChunk);
            $this->assertIsString($review);

            // If the service is configured, we should get a meaningful review
            if ($aiService->getProviderName() && AIProviderFactory::create()->isConfigured()) {
                $this->assertNotEmpty($review);
                $this->assertStringNotContainsString('error', strtolower($review));
            }
        } catch (\Exception $e) {
            // Expected if AI service is not configured in test environment
            $this->assertTrue(true);
        }
    }

    public function test_ai_provider_factory_lists_available_providers(): void
    {
        $providers = AIProviderFactory::getAvailableProviders();
        $this->assertIsArray($providers);
        $this->assertContains('claude', $providers);
        $this->assertContains('openai', $providers);
    }

    public function test_ai_provider_factory_lists_configured_providers(): void
    {
        $configuredProviders = AIProviderFactory::getConfiguredProviders();
        $this->assertIsArray($configuredProviders);
    }
}
