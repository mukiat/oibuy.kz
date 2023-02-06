<?php

namespace App\Custom\Guestbook\Controllers\Admin;

use App\Custom\BaseAdminController as Controller;
use Illuminate\Http\Request;

class TestAdminController extends Controller
{
    public function index(Request $request)
    {
        return 'admin';
    }

}
