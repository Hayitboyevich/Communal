<?php

namespace Modules\Water\Models;

use App\Models\Document;
use App\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class ProtocolHistory extends Model
{
    protected $guarded = false;

    protected $casts = [
        'content' => 'object',
    ];

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function formattedAdditionalInfo(): ?array
    {
        $info = $this->content->additionalInfo ?? null;
        if (empty($info)) {
            return null;
        }

        $info = json_decode(json_encode($info), true);
        if (!is_array($info)) {
            return null;
        }

        foreach (['images', 'additional_files', 'protocol_files', 'image_files'] as $key) {
            if (!empty($info[$key]) && is_array($info[$key])) {
                $info[$key] = array_values(array_map(fn($file) => [
                    'url' => Storage::disk('public')->url(is_array($file) ? ($file['url'] ?? '') : $file),
                ], $info[$key]));
            }
        }

        return $info;
    }
}
