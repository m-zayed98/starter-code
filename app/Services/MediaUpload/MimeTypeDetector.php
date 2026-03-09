<?php

namespace App\Services\MediaUpload;

use Illuminate\Http\UploadedFile;

/**
 * Classifies an uploaded file's MIME type into one of three categories:
 *
 *  - WEBP_CONVERTIBLE  → raster images that Intervention can decode and re-encode as WebP
 *                        (jpg, jpeg, png, bmp, tiff, tif)
 *  - NON_CONVERTIBLE   → images Intervention supports structurally but we keep as-is
 *                        (gif — animated frames would be lost; avif — limited encoder support)
 *  - GENERIC_FILE      → everything else (pdf, zip, doc, mp4, svg …)
 *                        → bypass Intervention, upload directly via Spatie
 *
 * Single Responsibility: only answers "what kind of file is this?"
 */
final class MimeTypeDetector
{
    /** MIME types we can safely convert to WebP without quality/fidelity loss. */
    private const WEBP_CONVERTIBLE_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/bmp',
        'image/tiff',
        'image/x-tiff',
        'image/x-bmp',
        'image/x-ms-bmp',
    ];

    /**
     * Image MIME types that Intervention *can* read but we intentionally preserve
     * in their original format (animated GIF frames, AVIF, WebP already).
     */
    private const KEEP_AS_IS_IMAGE_MIMES = [
        'image/gif',
        'image/avif',
        'image/webp',
    ];

    public static function isWebpConvertible(UploadedFile $file): bool
    {
        return in_array(
            self::normalizeMime($file),
            self::WEBP_CONVERTIBLE_MIMES,
            strict: true,
        );
    }

    public static function isKeepAsIsImage(UploadedFile $file): bool
    {
        return in_array(
            self::normalizeMime($file),
            self::KEEP_AS_IS_IMAGE_MIMES,
            strict: true,
        );
    }

    public static function isImage(UploadedFile $file): bool
    {
        return self::isWebpConvertible($file) || self::isKeepAsIsImage($file);
    }

    public static function isGenericFile(UploadedFile $file): bool
    {
        return ! self::isImage($file);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private static function normalizeMime(UploadedFile $file): string
    {
        // Prefer the real MIME detected from the file content (more reliable than
        // the client-supplied extension, which can be spoofed).
        return strtolower($file->getMimeType() ?? $file->getClientMimeType());
    }
}
