<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ExportHistory
 * @package App\Entities
 */
class ExportHistory extends Model
{
    /**
     * @var string
     */
    protected $table = 'export_history';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string[]
     */
    protected $fillable = [
        'ru_id',
        'file_name',
        'file_type',
        'download_params',
        'download_count',
    ];
}
