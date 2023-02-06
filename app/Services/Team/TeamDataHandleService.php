<?php

namespace App\Services\Team;

use App\Models\TeamLog;
use App\Repositories\Common\BaseRepository;

class TeamDataHandleService
{
    /**
     * 拼团日志
     *
     * @param array $team_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getTeamLogDataList($team_id = [], $data = [], $limit = 0)
    {
        $team_id = BaseRepository::getExplode($team_id);

        if (empty($team_id)) {
            return $team_id;
        }

        $team_id = $team_id ? array_unique($team_id) : [];

        $data = $data ? $data : '*';

        $res = TeamLog::select($data)->whereIn('team_id', $team_id)->where('status', 0);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['team_id']] = $row;
            }
        }

        return $arr;
    }
}