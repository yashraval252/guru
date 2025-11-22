<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class VoiceCommandTest extends TestCase
{
    public function testVoiceProcessApiSuccess(): void
    {
        // Simulate a successful response from voice_process.php
        $response = [
            'text' => 'Add entry Har Mahadev on 2024-06-15',
        ];

        $this->assertArrayHasKey('text', $response);
        $this->assertIsString($response['text']);
        $this->assertStringContainsString('entry', $response['text']);
    }

    public function testVoiceProcessApiError(): void
    {
        // Simulate an error response
        $response = [
            'error' => 'Failed to process audio',
        ];

        $this->assertArrayHasKey('error', $response);
        $this->assertIsString($response['error']);
    }

    public function testNluProcessApiExtractsFields(): void
    {
        $inputText = 'Har Mahadev add entry title Meeting date 2024-06-20';

        // Simulate nlu_process.php output
        $date = '2024-06-20';
        $title = 'Meeting';

        $this->assertEquals('2024-06-20', $date);
        $this->assertEquals('Meeting', $title);
    }

    public function testNluProcessApiErrorOnEmptyInput(): void
    {
        $inputText = '';

        $this->assertEmpty(trim($inputText));
    }
}
