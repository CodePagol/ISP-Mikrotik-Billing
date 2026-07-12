<?php

namespace App\Http\Controllers;

use App\Models\MainSiteData;
use App\Models\PackageList;

class MainSiteController extends Controller
{
    public function index()
    {
        $siteData = MainSiteData::getActive();
        $packages = PackageList::orderBy('sort_order')
            ->where('show_on_site', true)
            ->limit(10)
            ->get();

        $reviews = \App\Models\CustomerReview::with(['pppUser.customer'])
            ->where('show_on_site', true)
            ->latest()
            ->get();

        // Record the visitor hit
        try {
            \App\Models\SiteVisitor::create([
                'ip_address' => request()->ip(),
                'visited_date' => now()->toDateString(),
            ]);
        } catch (\Exception $e) {
            // Log warning but prevent app crash on DB errors
            \Log::warning('Visitor tracking failed: ' . $e->getMessage());
        }

        $uniqueVisitors = 2000 + \App\Models\SiteVisitor::distinct('ip_address')->count();
        $totalVisits = 5000 + \App\Models\SiteVisitor::count();

        return view('main-site', compact('siteData', 'packages', 'reviews', 'uniqueVisitors', 'totalVisits'));
    }

    public function allPackages()
    {
        $siteData = MainSiteData::getActive();
        $packages = PackageList::orderBy('sort_order')
            ->where('show_on_site', true)
            ->get();

        // Record the visitor hit
        try {
            \App\Models\SiteVisitor::create([
                'ip_address' => request()->ip(),
                'visited_date' => now()->toDateString(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('Visitor tracking failed in allPackages: ' . $e->getMessage());
        }

        $uniqueVisitors = 2000 + \App\Models\SiteVisitor::distinct('ip_address')->count();
        $totalVisits = 5000 + \App\Models\SiteVisitor::count();

        return view('all-packages', compact('siteData', 'packages', 'uniqueVisitors', 'totalVisits'));
    }
}
