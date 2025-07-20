<?php

namespace Tests\Feature;

use App\Models\ImportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

it('can get import request by id', function () {
// Create test import requests
    $importRequest1 = new ImportRequest();
    $importRequest1->forceFill([
        'original_file_name' => 'test1.csv',
        'status' => 'new',
        'file_path' => 'uploads/test1.csv'
    ])->save();

    $importRequest2 = new ImportRequest();

    $importRequest2->forceFill([
        'original_file_name' => 'test2.csv',
        'status' => 'completed',
        'file_path' => 'uploads/test2.csv'
    ])->save();


// Make GET request with id parameter
    $response = $this->getJson('/api/import-requests?id=' . $importRequest1->id);

// Assert response
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data'
    ]);

// Verify that only the requested import request is returned
    $responseData = $response->json('data');
    expect($responseData)->toBeArray();

// Check if it's a single item or array of items
    if (isset($responseData[0])) {
// Array of items
        expect(count($responseData))->toBe(1);
        expect($responseData[0]['id'])->toBe($importRequest1->id);
        expect($responseData[0]['original_file_name'])->toBe('test1.csv');
    } else {
// Single item
        expect($responseData['id'])->toBe($importRequest1->id);
        expect($responseData['original_file_name'])->toBe('test1.csv');
    }
});

it('can get import requests by status', function () {
// Create test import requests with different statuses
    (new ImportRequest())->forceFill([
        'original_file_name' => 'new1.csv',
        'status' => 'new',
        'file_path' => 'uploads/new1.csv'
    ])->save();

    (new ImportRequest())->forceFill([
        'original_file_name' => 'new2.csv',
        'status' => 'new',
        'file_path' => 'uploads/new2.csv'
    ])->save();

    (new ImportRequest())->forceFill([
        'original_file_name' => 'completed.csv',
        'status' => 'completed',
        'file_path' => 'uploads/completed.csv'
    ])->save();

// Make GET request with status parameter
    $response = $this->getJson('/api/import-requests?status=new');

// Assert response
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data'
    ]);

// Verify that only import requests with 'new' status are returned
    $responseData = $response->json('data');
    expect($responseData)->toBeArray();

    if (isset($responseData[0])) {
// Array of items
        expect(count($responseData))->toBe(2);
        foreach ($responseData as $item) {
            expect($item['status'])->toBe('new');
        }
    }
});

it('validates that either id or status is required', function () {
// Make GET request without any parameters
    $response = $this->getJson('/api/import-requests');

// Should return validation error
    $response->assertStatus(422)->assertJsonStructure([
        'success',
        'error'
    ]);

});

it('validates that status must be valid value', function () {
    $response = $this->getJson('/api/import-requests?status=invalid_status');

    $response->assertStatus(422);
    assert($response->json()['error']['message'], 'The selected status is invalid. Valid statuses are: new, in_progress, completed, error');
});

it('can handle non-existent id gracefully', function () {
    $response = $this->getJson('/api/import-requests?id=999999');
    $response->assertStatus(404);
});

it('can handle status with no matching records', function () {
// Create import request with different status
    (new ImportRequest())->forceFill([
        'original_file_name' => 'test.csv',
        'status' => 'completed',
        'file_path' => 'uploads/test.csv'
    ])->save();

// Query for different status
    $response = $this->getJson('/api/import-requests?status=error');

    $response->assertStatus(200);

    $responseData = $response->json('data.records');
    expect($responseData)->toBeArray();
    expect(count($responseData))->toBe(0);
});
