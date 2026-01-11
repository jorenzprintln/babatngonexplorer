<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminReportController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Report::with(['user', 'review.place', 'place', 'resolver'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $reports = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'reviewing' => Report::where('status', 'reviewing')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'dismissed' => Report::where('status', 'dismissed')->count(),
        ];

        return Inertia::render('Admin/Report', [
            'reports' => $reports,
            'stats' => $stats,
            'filters' => $request->only(['status', 'type', 'search']),
        ]);
    }

    public function show(Report $report): Response
    {
        $report->load(['user', 'review.place', 'place', 'resolver']);

        return Inertia::render('Admin/ReportDetail', [
            'report' => $report,
        ]);
    }

    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewing,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if (in_array($validated['status'], ['resolved', 'dismissed'])) {
            if ($validated['status'] === 'resolved') {
                $report->resolve(auth()->id(), $validated['admin_notes'] ?? null);
            } else {
                $report->dismiss(auth()->id(), $validated['admin_notes'] ?? null);
            }
        } else {
            $report->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? $report->admin_notes,
            ]);
        }

        return back()->with('success', 'Report status updated successfully.');
    }

    public function destroy(Report $report)
    {
        $report->delete();

        return back()->with('success', 'Report deleted successfully.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:reports,id',
            'status' => 'required|in:pending,reviewing,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $reports = Report::whereIn('id', $validated['report_ids'])->get();

        foreach ($reports as $report) {
            if (in_array($validated['status'], ['resolved', 'dismissed'])) {
                if ($validated['status'] === 'resolved') {
                    $report->resolve(auth()->id(), $validated['admin_notes'] ?? null);
                } else {
                    $report->dismiss(auth()->id(), $validated['admin_notes'] ?? null);
                }
            } else {
                $report->update([
                    'status' => $validated['status'],
                    'admin_notes' => $validated['admin_notes'] ?? null,
                ]);
            }
        }

        return back()->with('success', count($reports) . ' reports updated successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:reports,id',
        ]);

        $count = Report::whereIn('id', $validated['report_ids'])->delete();

        return back()->with('success', $count . ' reports deleted successfully.');
    }
}