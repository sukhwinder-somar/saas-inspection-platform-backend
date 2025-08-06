<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'template',
        'status',
        'published_at',
    ];

    protected $casts = [
        'content' => 'json',
        'published_at' => 'datetime',
    ];

    /**
     * Available page templates
     */
    public static function getAvailableTemplates(): array
    {
        return [
            'default' => 'Default Page',
            'home' => 'Homepage',
            'pricing' => 'Pricing Page',
            'about' => 'About Page',
            'contact' => 'Contact Page',
            'blog' => 'Blog Page',
            'feature' => 'Feature Page',
        ];
    }

    /**
     * Get the template view path
     */
    public function getTemplateViewPath(): string
    {
        $template = $this->template ?: 'default';
        return "pages.templates.{$template}";
    }

    /**
     * Scope for published pages
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Get page by slug
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}