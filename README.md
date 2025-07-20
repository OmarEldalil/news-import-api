# News Import API

A Laravel-based API service for importing news from CSV files with categories and asynchronous processing using message queue.

## Overview

This application provides a solution for importing news from CSV files. It processes uploaded CSV files asynchronously, creates news records with associated many-to-many categories, and tracks the import progress through a status system.

### Key Features

- **CSV File Upload & Validation**: Upload CSV files with automatic header validation
- **Asynchronous Processing**: Background job processing using Redis
- **CSV File handling**: To ensure handling a large file efficiently, the application uses streaming to read CSV files in chunks and not loading the entire file into memory.
- **News Creation**: Create news and associate them with categories IN BATCHES to optimize performance and minimize database queries.
- **Category Management**: Automatic category creation and many-to-many relationships when not found. having unique category names.
- **Import Tracking**: Status tracking with error reporting (New, In Progress, Completed, Error)
- **RESTful API**: Clean API endpoints for upload and status checking
- **Comprehensive Testing**: Feature tests ensuring end-to-end functionality

## Getting Started

### Prerequisites

- Docker and Docker Compose
- Git

### Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/OmarEldalil/news-import-api
   cd news-import-api
   ```

2. **Build and start the application**
   ```bash
   docker compose up -d --build
   ```
3. **Verify the setup**
   ```bash
   curl http://localhost:8000/api/health
   ```
   
### How to use

- A Postman collection is provided in the root directory as `news-import.postman_collection.json`, it has the main 3 endpoints for uploading CSV files and checking import status and downloading error report.
- A sample CSV file is provided in the root directory as `sample_sheet.csv`.

## Architecture

### Technology Stack

- **Framework**: Laravel 12
- **Database**: SQLite (for simplicity and development)
- **Message Broker**: Redis (for queue management)
- **Testing**: Pest (modern PHP testing framework)
- **Containerization**: Docker & Docker Compose

### Database Schema

The application uses three main tables:

1. **`import_requests`**: Tracks CSV upload and processing status
2. **`news`**: Stores news articles (title, content, url)
3. **`categories`**: Stores category information
4. **`category_news`**: Many-to-many pivot table linking news and categories

### CSV File Format

Expected CSV structure:
```csv
title,content,category,url
Breaking News,This is breaking news content,Politics-World,https://example.com/news1
Tech Update,Latest technology updates,Tech,https://example.com/news2
```

- **Categories**: Multiple categories can be specified using hyphen separation (e.g., "Politics-World-Sports")
- **Required Fields**: title, content, category, url

### Docker Services

The application runs three main services:

- **`api`**: Laravel application (PHP 8.4)
- **`redis`**: Redis server for queue management
- **`worker`**: Background job processor

## API Endpoints

### Upload CSV File
```http
POST /api/import-requests
Content-Type: multipart/form-data

{
  "news_file": <csv-file>
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Import request created successfully.",
  "data": {
    "id": 1,
    "original_file_name": "news.csv",
    "status": "new"
  }
}
```

### Get Import Status
```http
GET /api/import-requests?id=1
# OR
GET /api/import-requests?status=completed
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "original_file_name": "news.csv",
      "status": "completed",
      "processed_at": "2025-07-20 10:30:00",
      "error_report_path": null
    }
  ]
}
```

```json
{
    "success": true,
    "message": "Success",
    "data": {
        "records": [
            {
                "id": 1,
                "original_file_name": "news.csv",
                "status": "completed",
                "processed_at": "2025-07-20 10:30:00",
                "error_report_path": null
            }
        ],
        "cursor": "eyJpZCI6MTAsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0"
    }
}
```

### Import Status Values

- **`new`**: File uploaded, awaiting processing
- **`in_progress`**: Currently being processed
- **`completed`**: Successfully processed
- **`error`**: Processing failed and only valid data are inserted (error report available)

## Processing Flow

1. **File Upload**: CSV file is uploaded and validated
2. **Event Dispatch**: `ImportRequestCreated` event is fired
3. **Background Processing**: `ProcessNewsImportRequestFile` listener processes the file
4. **Data Creation**: News articles and categories are created
5. **Status Update**: Import request status is updated with results

## Testing

The application uses **Feature Tests** (integration tests) to ensure comprehensive coverage of the entire application flow.

### Testing Approach

Our testing strategy focuses on **Feature Tests** rather than unit tests because:

- **End-to-End Coverage**: Tests the complete user journey from API request to database persistence
- **Real Integration**: Tests actual file upload, event dispatch, and database operations
- **Business Logic Validation**: Ensures the entire business process works correctly
- **Regression Prevention**: Catches issues across the entire application stack

### Running Tests

```bash
# Run all tests
docker compose exec api php artisan test

# Run specific feature tests
docker compose exec api php artisan test tests/Feature/ImportNewsCSVFileTest.php
docker compose exec api php artisan test tests/Feature/ProcessCSVFileTest.php
```

### Queue Workers

The application uses Redis-backed queues for background processing:

```bash
# Monitor queue status
docker compose exec worker php artisan queue:monitor
```

### Database Management

```bash
# Run migrations
docker compose exec app php artisan migrate

# Reset database
docker compose exec app php artisan migrate:fresh

# Check database status
docker compose exec app php artisan migrate:status
```

### Logs

```bash
# Application logs
docker compose logs app

# Worker logs
docker compose logs worker

# Redis logs
docker compose logs redis
```

## Error Handling

The application provides comprehensive error handling:

- **CSV Validation Errors**: Invalid headers or file format
- **Processing Errors**: Database constraints or data validation issues
- **Storage Errors**: File system or storage-related problems
- **Queue Errors**: Background job processing failures

Error reports are generated as CSV files and accessible via the API when processing fails.
