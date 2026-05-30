<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $audits = Audit::with('user')->latest()->paginate(50);
        return view('audits.index', compact('audits'));
    }
}
