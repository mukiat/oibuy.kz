<?php

namespace App\Services\Mail;

use App\Models\MailTemplates;
use App\Repositories\Common\BaseRepository;

class MailTemplateManageService
{
    /**
     * 加载指定的模板内容
     *
     * @access  public
     * @param int $temp_id 邮件模板的ID
     * @return  array
     */
    public function loadTemplate($temp_id = 0)
    {
        $res = MailTemplates::where('template_id', $temp_id);
        $row = BaseRepository::getToArrayFirst($res);
        return $row;
    }
}
