<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display the specified page.
     */
    public function show(string $slug): View
    {
        $page = Page::published()
                   ->bySlug($slug)
                   ->firstOrFail();

        // Set SEO metadata if seo() helper is available
        if (function_exists('seo')) {
            seo()
                ->title($page->meta_title ?: $page->title)
                ->description($page->meta_description)
                ->canonical(url($page->slug));
        }

        // Return the appropriate template view
        $templateView = $page->getTemplateViewPath();

        // Check if the template view exists, fallback to default
        if (!view()->exists($templateView)) {
            $templateView = 'pages.templates.default';
        }

        return view($templateView, compact('page'));
    }

    /**
     * Display the homepage (special case)
     */
    public function home(): View
    {
        // Try to get homepage from database first
        $page = Page::published()
                   ->bySlug('home')
                   ->first();

        if ($page) {
            return $this->show('home');
        }

        // Fallback to static SaaS home view
        return view('livewire.saas-home');
    }
}
