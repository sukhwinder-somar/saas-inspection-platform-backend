<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    protected $fillable = [
        'page_id',
        'block_type',
        'data',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean'
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get available block types (ElementalArea style)
     */
    public static function getAvailableBlockTypes(): array
    {
        return [
            'hero' => [
                'label' => 'Hero Section',
                'icon' => 'heroicon-o-star',
                'description' => 'Large banner section with title, subtitle and CTA',
            ],
            'text' => [
                'label' => 'Rich Text',
                'icon' => 'heroicon-o-document-text',
                'description' => 'Rich text content with formatting',
            ],
            'features' => [
                'label' => 'Features Grid',
                'icon' => 'heroicon-o-squares-2x2',
                'description' => 'Grid of features with icons and descriptions',
            ],
            'cta' => [
                'label' => 'Call to Action',
                'icon' => 'heroicon-o-megaphone',
                'description' => 'Call to action section with button',
            ],
            'gallery' => [
                'label' => 'Image Gallery',
                'icon' => 'heroicon-o-photo',
                'description' => 'Image gallery with lightbox',
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'icon' => 'heroicon-o-chat-bubble-left-ellipsis',
                'description' => 'Customer testimonials slider',
            ],
            'pricing' => [
                'label' => 'Pricing Table',
                'icon' => 'heroicon-o-currency-dollar',
                'description' => 'Pricing plans comparison table',
            ],
            'contact' => [
                'label' => 'Contact Form',
                'icon' => 'heroicon-o-envelope',
                'description' => 'Contact form with validation',
            ],
        ];
    }

    /**
     * Render the block content
     */
    public function render(): string
    {
        $template = "pages.blocks.{$this->block_type}";
        
        if (!view()->exists($template)) {
            return '';
        }

        return view($template, [
            'data' => $this->data,
            'block' => $this
        ])->render();
    }
}