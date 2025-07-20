<?php

namespace Tests\Feature;

use App\Events\ImportRequestCreated;
use App\Models\ImportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

it('can upload csv file and create import request', function () {
    // Fake the event dispatcher
    Event::fake();

    // Create a test CSV file
    $csvContent = "title,content,category,url\n";
    $csvContent .= "Test News,Test content,Tech,https://example.com\n";
    $csvContent .= "Another News,More content,Politics,https://example2.com";

    $validCSVFile = UploadedFile::fake()->createWithContent('news.csv', $csvContent);

    // Make the POST request
    $response = $this->post('/api/import-requests', [
        'news_file' => $validCSVFile
    ]);

    // Assert the response
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'id',
            'original_file_name',
            'status'
        ]
    ]);

    // Assert that ImportRequestCreated event was dispatched
    Event::assertDispatched(ImportRequestCreated::class, function ($event) {
        return $event->importRequest instanceof ImportRequest;
    });

    // Assert that a record was created in the database
    expect(ImportRequest::count())->toBe(1);

    $importRequest = ImportRequest::first();
    expect($importRequest)
        ->not->toBeNull()
        ->and($importRequest->original_file_name)->toBe('news.csv');

    $this->assertDatabaseHas('import_requests', [
        'original_file_name' => 'news.csv',
        'status' => 'new'
    ]);
});

it('requires news_file parameter', function () {
    $response = $this->postJson('/api/import-requests', []);

    $response->assertStatus(422)
        ->assertJson([
            "success" => false,
            "error" => [
                "code" => "1001",
                "message" => "The news file field is required.",
                "errors" => [
                    "news_file" => [
                        0 => "The news file field is required."
                    ]
                ]
            ]
        ]);
});

it('requires csv file in correct format', function () {
    $csvContent = "title,content,category\nTest,Content,Tech";
    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    $response = $this->postJson('/api/import-requests', [
        'news_file' => $file
    ]);

    $response->assertStatus(422)->assertJson([
        "success" => false,
        "error" => [
            "code" => "1007",
            "message" => "The CSV header does not match the expected format. Missing columns: url"
        ]
    ]);
});

