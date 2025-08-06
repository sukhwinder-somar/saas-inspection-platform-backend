@extends('layouts.saas')

@section('title', 'Organizations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Organizations</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Manage tenant organizations</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <p class="text-gray-600 dark:text-gray-400">
                This page will display organization management functionality. 
                For now, use the Filament admin panel to manage organizations.
            </p>
            
            <div class="mt-4">
                <a href="/admin/organizations" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Go to Admin Panel
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
