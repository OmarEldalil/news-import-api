<?php

namespace Tests\Feature;

use App\Events\ImportRequestCreated;
use App\Listeners\ProcessNewsImportRequestFile;
use App\Models\Category;
use App\Models\ImportRequest;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    Queue::fake();
});

it('processes import request and creates news with categories when event is dispatched', function () {
    // Create a test CSV file with news data
    $csvContent = "title,content,category,url\n";
    $csvContent .= "Breaking News,This is breaking news content,Politics-World,https://example.com/news1\n";
    $csvContent .= "Tech Update,Latest technology updates,Tech,https://example.com/news2\n";
    $csvContent .= "Sports News,Football match results,Sports-World,https://example.com/news3";

    $file = UploadedFile::fake()->createWithContent('news.csv', $csvContent);

    // Store the file and create ImportRequest manually
    $storagePath = $file->store('import-requests');

    $importRequest = new ImportRequest();
    $importRequest->original_file_name = 'news.csv';
    $importRequest->status = 'new';
    $importRequest->file_path = $storagePath;
    $importRequest->save();

    // Assert initial state - no news or categories exist
    expect(News::count())->toBe(0);
    expect(Category::count())->toBe(0);

    // Create and dispatch the event
    $event = new ImportRequestCreated($importRequest);

    // Manually trigger the listener (since we're testing the listener directly)
    $listener = app(ProcessNewsImportRequestFile::class);
    $listener->handle($event);

    // Refresh the import request to get updated status
    $importRequest->refresh();

    // Assert that news records were created
    expect(News::count())->toBe(3);

    // Assert that categories were created
    $expectedCategories = ['Politics', 'World', 'Tech', 'Sports'];
    expect(Category::count())->toBe(4);

    foreach ($expectedCategories as $categoryName) {
        expect(Category::where('name', $categoryName)->exists())->toBeTrue();
    }

    // Assert specific news records
    $news1 = News::where('title', 'Breaking News')->first();
    expect($news1)->not->toBeNull();
    expect($news1->content)->toBe('This is breaking news content');
    expect($news1->url)->toBe('https://example.com/news1');

    // Assert news-category relationships
    $news1Categories = $news1->categories->pluck('name')->toArray();
    expect($news1Categories)->toContain('Politics');
    expect($news1Categories)->toContain('World');

    // Assert import request status was updated to completed
    expect($importRequest->status)->toBe('completed');
});

it('handles invalid CSV data and updates import request status to error', function () {
    // Create CSV with some invalid data (missing required fields)
    $csvContent = "title,content,category,url\n";
    $csvContent .= "Valid News,Valid content,Tech,https://example.com\n";
    $csvContent .= ",Missing title content,Sports,https://example2.com\n"; // Invalid: missing title
    $csvContent .= "Another News,,Politics,https://example3.com"; // Invalid: missing content

    $file = UploadedFile::fake()->createWithContent('invalid_news.csv', $csvContent);
    $storagePath = $file->store('import-requests');

    $importRequest = new ImportRequest();
    $importRequest->original_file_name = 'invalid_news.csv';
    $importRequest->status = 'new';
    $importRequest->file_path = $storagePath;
    $importRequest->save();

    // Dispatch the event
    $event = new ImportRequestCreated($importRequest);
    $listener = app(ProcessNewsImportRequestFile::class);
    $listener->handle($event);

    $importRequest->refresh();

    // Assert that only valid news was created
    expect(News::count())->toBe(1);
    expect(News::where('title', 'Valid News')->exists())->toBeTrue();

    // Assert import request status indicates completion (with or without errors)
    expect($importRequest->status)->toBe('error');
    expect($importRequest->processed_at)->not->toBeNull();
    expect($importRequest->error_report_path)->not->toBeNull();


    $errorCsvContent = Storage::disk('public')->get(str_replace('/storage', '', $importRequest->error_report_path));
    expect($errorCsvContent)->toBe("column_number,error\n2,The title field is required.\n3,The content field is required.\n");
});

it('ensures listener implements ShouldQueue interface for background processing', function () {
    $listener = new ProcessNewsImportRequestFile(app(\App\Services\ImportRequestService::class));

    expect($listener)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});
