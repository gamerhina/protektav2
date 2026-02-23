<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LandingPageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hero_title',
        'hero_super_title',
        'app_name',
        'hero_subtitle',
        'app_description',
        'cta_label',
        'cta_link',
        'schedule_heading',
        'logo_path',
        'favicon_path',
        'app_icon_path',
        'login_background_path',
        'landing_background_path',
        'landing_background_slides',
        'landing_slider_enabled',
        'landing_slider_interval_ms',
        'content_background_path',
        'header_height',
        'landing_background_opacity',
        'content_background_opacity',
        'primary_color',
        'secondary_color',
        'accent_color',
        'button_color',
        'table_header_from',
        'table_header_to',
        'table_header_text_color',
        'table_row_even_color',
        'table_row_odd_color',
        'table_row_text_color',
        'table_border_color',
        'header_overlay_from',
        'header_overlay_to',
        'hero_overlay_opacity',
    ];

    protected $casts = [
        'landing_background_slides' => 'array',
        'landing_slider_enabled' => 'boolean',
        'landing_slider_interval_ms' => 'integer',
    ];

    protected $appends = [
        'logo_url',
        'favicon_url',
        'login_background_url',
        'landing_background_url',
        'landing_background_slide_urls',
        'content_background_url',
        'app_icon_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('uploads')->url($this->logo_path) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon_path ? Storage::disk('uploads')->url($this->favicon_path) : null;
    }

    public function getLoginBackgroundUrlAttribute(): ?string
    {
        return $this->login_background_path ? Storage::disk('uploads')->url($this->login_background_path) : null;
    }

    public function getLandingBackgroundUrlAttribute(): ?string
    {
        return $this->landing_background_path ? Storage::disk('uploads')->url($this->landing_background_path) : null;
    }

    public function getLandingBackgroundSlideUrlsAttribute(): array
    {
        $slides = $this->landing_background_slides;

        if (!is_array($slides) || count($slides) === 0) {
            return $this->landing_background_path ? [Storage::disk('uploads')->url($this->landing_background_path)] : [];
        }

        return array_values(array_filter(array_map(function ($path) {
            if (!is_string($path) || $path === '') {
                return null;
            }

            return Storage::disk('uploads')->url($path);
        }, $slides)));
    }

    public function getContentBackgroundUrlAttribute(): ?string
    {
        return $this->content_background_path ? Storage::disk('uploads')->url($this->content_background_path) : null;
    }

    public function getAppIconUrlAttribute(): ?string
    {
        return $this->app_icon_path ? Storage::disk('uploads')->url($this->app_icon_path) : null;
    }
}
