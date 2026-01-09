<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index()
    {
        $logs = PaymentLog::latest()->get();
        return view('admin.logs.index', compact('logs'));
    }
}
