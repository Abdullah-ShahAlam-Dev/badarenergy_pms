<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FileStorage;
use App\Helper\Files;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class MigrateFromCloudinary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-from-cloudinary 
                            {--dry-run : Simulate the reverse migration without downloading or deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reverse migration: download all migrated files from Cloudinary back to the local VPS storage.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Starting reverse migration from Cloudinary to Local storage...");

        $query = FileStorage::where('storage_location', 'cloudinary');
        $totalFiles = $query->count();

        if ($totalFiles === 0) {
            $this->info("No Cloudinary files found to reverse-migrate.");
            Cache::forget('cloudinary_migration_completed');
            return Command::SUCCESS;
        }

        $this->info("Found {$totalFiles} files to reverse-migrate.");
        $successCount = 0;
        $failedCount = 0;

        // Process in chunks of 100
        $query->chunkById(100, function ($files) use (&$successCount, &$failedCount, $totalFiles) {
            foreach ($files as $file) {
                $localFolder = public_path(Files::UPLOAD_FOLDER . '/' . $file->path);
                $localPath = $localFolder . '/' . $file->filename;
                $cloudPath = $file->path . '/' . $file->filename;

                $index = $successCount + $failedCount + 1;
                $this->info("[{$index}/{$totalFiles}] Reversing: {$cloudPath}");

                if ($this->option('dry-run')) {
                    $this->comment("[DRY RUN] Would reverse migrate {$file->filename}");
                    $successCount++;
                    continue;
                }

                try {
                    // 1. Download file contents from Cloudinary
                    if (!Storage::disk('cloudinary')->exists($cloudPath)) {
                        $this->warn("File missing from Cloudinary: {$cloudPath}. Skipping reverse migration for this file.");
                        continue;
                    }

                    $fileContents = Storage::disk('cloudinary')->get($cloudPath);

                    // 2. Ensure local directory exists
                    if (!File::exists($localFolder)) {
                        File::makeDirectory($localFolder, 0775, true);
                    }

                    // 3. Save file locally on the VPS
                    File::put($localPath, $fileContents);

                    // 4. Update Database location to 'local'
                    $file->storage_location = 'local';
                    $file->save();

                    // 5. Delete file from Cloudinary
                    $isImageOrVideo = in_array(strtolower(pathinfo($file->filename, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'mp4', 'mov', 'avi']);
                    $publicId = $file->path . '/' . ($isImageOrVideo ? preg_replace('/\.[^.]+$/', '', $file->filename) : $file->filename);
                    $resourceType = $isImageOrVideo ? 'image' : 'raw';

                    cloudinary()->uploadApi()->destroy($publicId, ['resource_type' => $resourceType]);

                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to reverse-migrate {$file->filename}: " . $e->getMessage());
                    $failedCount++;
                }
            }
        });

        $this->info("----------------------------------------");
        $this->info("Reverse Migration Complete Summary:");
        $this->info("Successfully Reverted: {$successCount}");
        $this->info("Failed: {$failedCount}");
        $this->info("----------------------------------------");

        if (!$this->option('dry-run')) {
            Cache::forget('cloudinary_migration_completed');
        }

        return Command::SUCCESS;
    }
}
