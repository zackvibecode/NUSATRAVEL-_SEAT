<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRun extends Model
{
    protected $fillable = [
        'source',
        'filename',
        'dropbox_path',
        'dry_run',
        'status',
        'packages_created',
        'packages_updated',
        'departures_created',
        'departures_updated',
        'registrations_created',
        'registrations_updated',
        'skipped',
        'errors',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'dry_run' => 'boolean',
            'errors' => 'array',
            'summary' => 'array',
        ];
    }
}
