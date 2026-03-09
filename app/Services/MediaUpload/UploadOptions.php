<?php

namespace App\Services\MediaUpload;

/**
 * Immutable value object holding all upload configuration options.
 */
final class UploadOptions
{
    public function __construct(
        public readonly int     $quality         = 80,
        public readonly ?int    $resizeWidth     = null,
        public readonly ?int    $resizeHeight    = null,
        public readonly bool    $maintainAspect  = true,
        public readonly bool    $useFit          = false,
        public readonly string  $collection      = 'default',
        public readonly ?string $customName      = null,
        public readonly array   $customProperties = [],
    ) {}

    // ── Fluent named constructors ─────────────────────────────────────────

    public static function defaults(): self
    {
        return new self();
    }

    public function withQuality(int $quality): self
    {
        return new self(
            quality: $quality,
            resizeWidth: $this->resizeWidth,
            resizeHeight: $this->resizeHeight,
            maintainAspect: $this->maintainAspect,
            useFit: $this->useFit,
            collection: $this->collection,
            customName: $this->customName,
            customProperties: $this->customProperties,
        );
    }

    public function withResize(int $width, ?int $height = null, bool $maintainAspect = true): self
    {
        return new self(
            quality: $this->quality,
            resizeWidth: $width,
            resizeHeight: $height,
            maintainAspect: $maintainAspect,
            useFit: false,
            collection: $this->collection,
            customName: $this->customName,
            customProperties: $this->customProperties,
        );
    }

    public function withFit(int $width, int $height): self
    {
        return new self(
            quality: $this->quality,
            resizeWidth: $width,
            resizeHeight: $height,
            maintainAspect: $this->maintainAspect,
            useFit: true,
            collection: $this->collection,
            customName: $this->customName,
            customProperties: $this->customProperties,
        );
    }

    public function withCollection(string $collection): self
    {
        return new self(
            quality: $this->quality,
            resizeWidth: $this->resizeWidth,
            resizeHeight: $this->resizeHeight,
            maintainAspect: $this->maintainAspect,
            useFit: $this->useFit,
            collection: $collection,
            customName: $this->customName,
            customProperties: $this->customProperties,
        );
    }

    public function withName(string $name): self
    {
        return new self(
            quality: $this->quality,
            resizeWidth: $this->resizeWidth,
            resizeHeight: $this->resizeHeight,
            maintainAspect: $this->maintainAspect,
            useFit: $this->useFit,
            collection: $this->collection,
            customName: $name,
            customProperties: $this->customProperties,
        );
    }

    public function withProperties(array $properties): self
    {
        return new self(
            quality: $this->quality,
            resizeWidth: $this->resizeWidth,
            resizeHeight: $this->resizeHeight,
            maintainAspect: $this->maintainAspect,
            useFit: $this->useFit,
            collection: $this->collection,
            customName: $this->customName,
            customProperties: $properties,
        );
    }

    public function needsProcessing(): bool
    {
        return $this->resizeWidth !== null || $this->resizeHeight !== null;
    }
}
