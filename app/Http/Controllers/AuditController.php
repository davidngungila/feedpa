<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditController extends Controller
{
    public function index()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $query = Audit::with('user');
        
        // Apply search filter
        if (request()->filled('search')) {
            $search = request()->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply date filters
        if (request()->filled('start_date')) {
            $query->whereDate('created_at', '>=', request()->start_date);
        }
        
        if (request()->filled('end_date')) {
            $query->whereDate('created_at', '<=', request()->end_date);
        }
        
        $audits = $query->latest()->paginate(50);
        
        $availableColumns = [
            'id' => 'ID',
            'created_at' => 'Date & Time',
            'user_name' => 'User',
            'action' => 'Action',
            'details' => 'Details',
            'ip_address' => 'IP Address',
            'country' => 'Country',
            'city' => 'City',
            'device_type' => 'Device Type',
            'device_browser' => 'Browser',
            'device_platform' => 'Platform',
            'url' => 'URL'
        ];
        
        $selectedColumns = request()->get('columns', array_keys($availableColumns));
        
        return view('audits.index', compact('audits', 'availableColumns', 'selectedColumns'));
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $query = Audit::with('user')->latest();
        
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $audits = $query->get()->map(function ($audit) {
            return [
                'id' => $audit->id,
                'created_at' => $audit->created_at,
                'user_name' => $audit->user?->name ?? 'Guest / System',
                'user_email' => $audit->user?->email ?? '-',
                'action' => $audit->action,
                'details' => $audit->details,
                'ip_address' => $audit->ip_address,
                'country' => $audit->country,
                'city' => $audit->city,
                'device_type' => $audit->device_type,
                'device_browser' => $audit->device_browser,
                'device_platform' => $audit->device_platform,
                'url' => $audit->url
            ];
        });
        
        $pdf = Pdf::loadView('audits.exports.pdf', compact('audits'))->setPaper('a4', 'landscape');
        return $pdf->download('audit-logs-' . date('Y-m-d') . '.pdf');
    }

    public function destroy($id)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $audit = Audit::findOrFail($id);
        $audit->delete();
        
        return back()->with('success', 'Audit log deleted successfully!');
    }

    public function bulkDestroy(Request $request)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'ids' => 'required|string'
        ]);
        
        $ids = json_decode($request->ids, true);
        
        if (!is_array($ids)) {
            return back()->with('error', 'Invalid audit log IDs provided!');
        }
        
        Audit::whereIn('id', $ids)->delete();
        
        return back()->with('success', 'Selected audit logs deleted successfully!');
    }
}
