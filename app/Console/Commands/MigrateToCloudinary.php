<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FileStorage;
use App\Helper\Files;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class MigrateToCloudinary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-to-cloudinary 
                            {--dry-run : Simulate the migration without executing uploads or deletes}
                            {--skip-search : Skip the Cloudinary Search API existence precheck and rely on idempotent overwrites}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate local uploaded files to Cloudinary safely with verification and rate limit safeguards.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Starting local file migration to Cloudinary...");

        if (config('filesystems.default') !== 'cloudinary') {
            $this->error("Current default storage driver is not set to 'cloudinary'. Please enable Cloudinary in Storage Settings first.");
            return Command::FAILURE;
        }

        $query = FileStorage::where('storage_location', 'local');
        $totalFiles = $query->count();

        if ($totalFiles === 0) {
            $this->info("No local files found to migrate.");
            Cache::forever('cloudinary_migration_completed', true);
            return Command::SUCCESS;
        }

        $this->info("Found {$totalFiles} local files to migrate.");
        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;
        $missingCount = 0;

        // Process in chunks of 100 for memory efficiency
        $query->chunkById(100, function ($files) use (&$successCount, &$failedCount, &$skippedCount, &$missingCount, $totalFiles) {
            foreach ($files as $file) {
                $localPath = public_path(Files::UPLOAD_FOLDER . '/' . $file->path . '/' . $file->filename);
                $cloudPath = $file->path . '/' . $file->filename;

                $index = $successCount + $failedCount + $skippedCount + $missingCount + 1;
                $this->info("[{$index}/{$totalFiles}] Processing: {$cloudPath}");

                // 1. Safety Check: If physical local file is missing, log and skip
                if (!File::exists($localPath)) {
                    $this->warn("Local file missing from disk: {$localPath}. Logging and keeping DB reference.");
                    $missingCount++;
                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->comment("[DRY RUN] Would migrate {$file->filename}");
                    $successCount++;
                    continue;
                }

                // 2. Existence Precheck (Interruption & Orphaning Reconciliation)
                $alreadyExists = false;
                if (!$this->option('skip-search')) {
                    $isImageOrVideo = in_array(strtolower(pathinfo($cloudPath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'mp4', 'mov', 'avi']);
                    $publicId = $isImageOrVideo ? preg_replace('/\.[^.]+$/', '', $cloudPath) : $cloudPath;

                    try {
                        $result = cloudinary()->search()
                            ->expression("public_id:\"$publicId\"")
                            ->execute();
                        $alreadyExists = !empty($result['resources']);
                    } catch (\Exception $e) {
                        $this->warn("Search API failed for {$publicId}: " . $e->getMessage() . ". Falling back to overwrite upload.");
                    }
                }

                if ($alreadyExists) {
                    $this->info("File already exists on Cloudinary. Reconciling database record...");
                    $file->storage_location = 'cloudinary';
                    $file->save();

                    // Safely delete local file
                    File::delete($localPath);
                    $skippedCount++;
                    continue;
                }

                // 3. Upload File
                try {
                    $fileContents = File::get($localPath);

                    // Idempotent upload via Cloudinary driver
                    $uploaded = Storage::disk('cloudinary')->put($cloudPath, $fileContents, [
                        'public_id' => preg_replace('/\.[^.]+$/', '', $cloudPath),
                        'overwrite' => true,
                        'resource_type' => 'auto'
                    ]);

                    if ($uploaded) {
                        // 4. Update Database Reference
                        $file->storage_location = 'cloudinary';
                        $file->save();

                        // 5. Delete Local VPS Copy
                        File::delete($localPath);
                        $successCount++;
                    } else {
                        throw new \Exception("Upload returned empty status.");
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to migrate {$file->filename}: " . $e->getMessage());
                    $failedCount++;
                }
            }
        });

        $this->info("----------------------------------------");
        $this->info("Migration Complete Summary:");
        $this->info("Successfully Migrated: {$successCount}");
        $this->info("Skipped (Already on Cloudinary): {$skippedCount}");
        $this->info("Failed: {$failedCount}");
        $this->info("Missing Local Files: {$missingCount}");
        $this->info("----------------------------------------");

        if ($failedCount === 0 && !$this->option('dry-run')) {
            $this->info("All files successfully migrated! Setting cloudinary_migration_completed = true.");
            Cache::forever('cloudinary_migration_completed', true);
        } else {
            $this->warn("Some files failed to migrate. Keep migration check active.");
        }

        return Command::SUCCESS;
    }
}
