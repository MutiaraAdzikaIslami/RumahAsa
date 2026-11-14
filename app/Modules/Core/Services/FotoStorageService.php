<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Exceptions\FileValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/**
 * FR-11b: Foto storage service.
 * Validasi tipe berdasarkan isi file, max 5MB, nama acak, simpan di disk private.
 */
class FotoStorageService
{
    private ImageManager $imageManager;

    public function __construct(
        private readonly string $disk,
        private readonly int $maxSize,
        private readonly int $maxPerKomponen,
    ) {
        $this->imageManager = new ImageManager(new GdDriver());
    }

    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const EXT_BY_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public function simpan(UploadedFile $file, string $directory, ?int $currentFotoCount = 0): array
    {
        $this->validate($file, $currentFotoCount);

        $mime = $file->getMimeType();
        $ext  = self::EXT_BY_MIME[$mime] ?? 'jpg';
        $namaAcak = Str::random(40) . '.' . $ext;
        $relativePath = trim($directory, '/') . '/' . $namaAcak;

        $image = $this->imageManager->read($file->getRealPath());
        if ($image->width() > 1920) {
            $image->scale(width: 1920);
        }
        $encoded = $this->encodeByMime($image, $mime);
        Storage::disk($this->disk)->put($relativePath, $encoded);

        return [
            'path_file'   => $relativePath,
            'mime_type'   => $mime,
            'ukuran_byte' => strlen($encoded),
            'nama_asli'   => $file->getClientOriginalName(),
        ];
    }

    public function sajikan(string $path)
    {
        abort_unless(Storage::disk($this->disk)->exists($path), 404, 'Foto tidak ditemukan.');
        return Storage::disk($this->disk)->response($path, null, [
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function hapus(string $path): void
    {
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    private function validate(UploadedFile $file, int $currentCount): void
    {
        if ($currentCount >= $this->maxPerKomponen) {
            throw FileValidationException::maxPerKomponen($this->maxPerKomponen);
        }
        if ($file->getSize() > $this->maxSize) {
            throw FileValidationException::tooLarge($file->getSize(), $this->maxSize);
        }
        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw FileValidationException::invalidType($mime);
        }
    }

    private function encodeByMime($image, string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => (string) $image->toJpeg(quality: 85),
            'image/png'  => (string) $image->toPng(),
            'image/webp' => (string) $image->toWebp(quality: 85),
            default      => (string) $image->toJpeg(quality: 85),
        };
    }
}