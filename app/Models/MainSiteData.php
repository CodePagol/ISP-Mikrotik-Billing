<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MainSiteData extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
    ];

    /**
     * Get a specific value by its type name, returning the decoded array or string.
     */
    public static function getValue(string $type, $default = null)
    {
        try {
            $active = self::getActive();
            if (property_exists($active, $type)) {
                return $active->$type;
            }
        } catch (\Throwable $e) {
            // Fallback to database query if cache fails
        }

        $record = self::where('type', $type)->first();

        if (! $record) {
            return $default;
        }

        // Return array if JSON, otherwise string
        $raw = $record->getRawOriginal('value');
        $decoded = @json_decode($raw, true);

        return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : $record->value;
    }

    /**
     * Update or create a specific key-value record
     */
    public static function setValue(string $type, $value): self
    {
        // Encode to JSON if array
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return self::updateOrCreate(
            ['type' => $type],
            ['value' => $value]
        );
    }

    /**
     * Get all active data and return it as an object
     * so that existing Blade templates ($siteData->hero_title) don't break.
     */
    public static function getActive()
    {
        return Cache::rememberForever('main_site_data_active', function () {
            $data = new \stdClass;
            $records = self::all();

            foreach ($records as $record) {
                // Return array if JSON, otherwise string
                $raw = $record->getRawOriginal('value');
                $decoded = @json_decode($raw, true);
                $value = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : $record->value;
                $key = $record->type;
                $data->$key = $value;
            }

            // Defaults in case they don't exist in DB
            $defaults = [
                'is_active' => true,
                'hero_title' => null,
                'hero_subtitle' => null,
                'hero_slides' => [],
                'about_tagline' => null,
                'about_title' => null,
                'about_body' => null,
                'services' => [],
                'gallery_items' => [],
                'packages_section_title' => null,
                'packages_section_subtitle' => null,
                'registration_link' => null,
                'team_title' => null,
                'team_subtitle' => null,
                'team_members' => [],
                'blog_title' => null,
                'blog_subtitle' => null,
                'blog_posts' => [],
                'testimonial_title' => null,
                'testimonial_subtitle' => null,
                'testimonials' => [],
                'contact_title' => null,
                'contact_subtitle' => null,
                'google_map_embed' => null,
                'footer_copyright' => null,
                'social_facebook' => null,
                'social_twitter' => null,
                'social_instagram' => null,
                'social_youtube' => null,
                'social_whatsapp' => null,
                'valuable_clients' => [],
                'theme_preset' => 'fintech',
                'theme_name' => 'ocean_blue',
                'theme_primary_color' => '#0284c7',
                'theme_accent_color' => '#38bdf8',
                'theme_card_style' => 'glass',
                'theme_border_radius' => '16px',
                'theme_font_size' => 'medium',
                'theme_font_family' => 'Outfit',
                'theme_nav_style' => 'sidebar',
                'theme_widget_style' => 'glass',
                'theme_mode' => 'dark',
                'theme_transparency' => '0.5',
                'theme_blur' => '16px',
                'theme_animations' => '1.0',
                'theme_gradient_intensity' => '0.7',
            ];

            foreach ($defaults as $key => $defaultValue) {
                if (! property_exists($data, $key)) {
                    $data->$key = $defaultValue;
                }
            }

            return $data;
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::flush());
        static::updated(fn () => Cache::flush());
        static::deleted(fn () => Cache::flush());
    }
}
