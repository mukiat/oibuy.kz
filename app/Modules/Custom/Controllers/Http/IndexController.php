<?php

namespace App\Modules\Wxapp\Controllers\Http;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function index()
    {
        return $this->display();
    }
}
