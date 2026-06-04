<?php

namespace Tests\Feature;

use App\Models\Berita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BeritaImageUploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that news image is correctly moved from temp to berita/{id} on creation,
     * and old files are deleted on update or delete.
     */
    public function test_news_image_upload_and_organization_flow(): void
    {
        Storage::fake('cms');

        // 1. Simulate Filament saving: file uploaded to berita/temp/
        $filename = uniqid('berita_') . '_12345.webp';
        $tempPath = "berita/temp/{$filename}";
        Storage::disk('cms')->put($tempPath, 'fake_webp_content');

        // Simulate a RichEditor image attachment uploaded to berita/temp/
        $richImageName = uniqid('berita_content_') . '_9999.webp';
        $tempRichPath = "berita/temp/{$richImageName}";
        Storage::disk('cms')->put($tempRichPath, 'fake_rich_image_content');

        // RichEditor content contains the temp image path
        $richContent = "<p>Ini isi berita.</p><img src=\"http://localhost/assets/{$tempRichPath}\"><p>Terima kasih.</p>";

        // 2. Create the news record
        $berita = Berita::create([
            'judul' => 'Test Judul Berita',
            'slug' => 'test-judul-berita',
            'excerpt' => 'Ringkasan berita.',
            'konten' => $richContent,
            'gambar' => $tempPath,
            'tanggal' => now(),
            'is_published' => true,
        ]);

        // Assert main file was moved from temp to berita/{id}/
        $expectedPath = "berita/{$berita->id}/{$filename}";
        Storage::disk('cms')->assertExists($expectedPath);
        Storage::disk('cms')->assertMissing($tempPath);

        // Assert database record was updated for main image
        $this->assertEquals($expectedPath, $berita->fresh()->gambar);

        // Assert rich text image was moved from temp to berita/{id}/
        $expectedRichPath = "berita/{$berita->id}/{$richImageName}";
        Storage::disk('cms')->assertExists($expectedRichPath);
        Storage::disk('cms')->assertMissing($tempRichPath);

        // Assert database record was updated for rich text content
        $expectedContent = "<p>Ini isi berita.</p><img src=\"http://localhost/assets/{$expectedRichPath}\"><p>Terima kasih.</p>";
        $this->assertEquals($expectedContent, $berita->fresh()->konten);

        // 3. Simulate image replacement and removal during update
        $newFilename = uniqid('berita_') . '_67890.webp';
        $newPath = "berita/{$berita->id}/{$newFilename}";
        Storage::disk('cms')->put($newPath, 'new_fake_webp_content');

        // Update the record: change main image, and remove the image from rich content
        $berita->update([
            'gambar' => $newPath,
            'konten' => "<p>Ini isi berita.</p><p>Terima kasih.</p>" // image tag removed!
        ]);

        // Assert old main file was deleted and new main file exists
        Storage::disk('cms')->assertMissing($expectedPath);
        Storage::disk('cms')->assertExists($newPath);

        // Assert the removed rich text image was deleted from storage
        Storage::disk('cms')->assertMissing($expectedRichPath);

        // 4. Simulate record deletion
        $berita->delete();

        // Assert folder/directory and all files are deleted
        Storage::disk('cms')->assertMissing($newPath);
        Storage::disk('cms')->assertMissing("berita/{$berita->id}");
    }
}
