<?php

namespace App\Custom\Guestbook\Controllers\Seller;

use App\Custom\BaseAdminController as Controller;
use Illuminate\Http\Request;

class TestSellerController extends Controller
{
    public function index(Request $request)
    {
        return 'seller';
    }

}
