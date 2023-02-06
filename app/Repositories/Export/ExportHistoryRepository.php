<?php

namespace App\Repositories\Export;

use Illuminate\Support\Facades\DB;

/**
 * Class ExportHistoryRepository
 * @package App\Repositories\Export
 */
class ExportHistoryRepository
{
    /**
     * export_list
     * @param int $ru_id
     * @param string $type
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function export_list($ru_id = 0, $type = '')
    {
        $export_history = DB::table('export_history')
            ->where('ru_id', $ru_id)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate();

        return $export_history;
    }

    /**
     * export_info
     *
     * @param int $ru_id
     * @param int $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public static function export_info($ru_id = 0, $id = 0, $columns = [])
    {
        $model = DB::table('export_history')
            ->where('ru_id', $ru_id)
            ->where('id', $id);

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        return $model->first();
    }

    /**
     * delete
     *
     * @param int $ru_id
     * @param int $id
     * @return int
     */
    public static function delete($ru_id = 0, $id = 0)
    {
        return DB::table('export_history')
            ->where('ru_id', $ru_id)
            ->where('id', $id)
            ->delete();
    }
}
