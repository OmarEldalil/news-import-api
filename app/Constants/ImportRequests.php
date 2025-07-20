<?php

namespace App\Constants;

class ImportRequests
{
    const string NEW = 'new';
    const string IN_PROGRESS = 'in_progress';
    const string COMPLETED = 'completed';
    const string ERROR = 'error';

    const array STATUS_LABELS = [
        self::NEW,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::ERROR,
    ];

    const array REQUIRED_CSV_HEADERS = [
        'title',
        'content',
        'category',
        'url'
    ];

    const string IMPORT_REQUESTS_CSV_FILES_DIRECTORY = 'import_requests/csv_files';
    const string ERRORS_CSV_FILES_DIRECTORY = 'errors/csv_files';

    const int CHUNK_SIZE = 500;

    const int PAGINATION_PER_PAGE = 10;
}
