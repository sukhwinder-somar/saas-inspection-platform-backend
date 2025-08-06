<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SaasHome extends Component
{
    public function render()
    {
        // Set SEO metadata for the home page if seo() helper is available
        if (function_exists('seo')) {
            seo()
                ->title(config('app.name') . ' - Asset Management & Inspection Platform')
                ->description('Comprehensive SaaS platform for asset tracking, inspections, compliance monitoring, and team collaboration. Streamline your operations with our modern enterprise solution.')
                ->canonical(route('home'))
                ->image(asset('images/og-home.jpg'));
        }

        return view('livewire.saas-home');
    }
}
