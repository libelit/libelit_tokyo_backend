<?php

namespace App\Jobs\Developer;

use App\Enums\ProjectStatusEnum;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreProjectPhotoJob
{
    protected User $user;
    protected int $projectId;
    protected array $photos;

    private const MAX_PHOTOS_PER_PROJECT = 10;

    public function __construct(User $user, int $projectId, array $photos)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->photos = $photos;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            $project = $developerProfile->projects()->find($this->projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            // Check if project is in editable state
            if (!in_array($project->status, [ProjectStatusEnum::DRAFT, ProjectStatusEnum::REJECTED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add photos to a project that is not in draft or rejected status.',
                ], 403);
            }

            // Check photo limit
            $currentPhotoCount = $project->photos()->count();
            $newPhotoCount = count($this->photos);

            if (($currentPhotoCount + $newPhotoCount) > self::MAX_PHOTOS_PER_PROJECT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum ' . self::MAX_PHOTOS_PER_PROJECT . ' photos allowed per project. You currently have ' . $currentPhotoCount . ' photos.',
                ], 422);
            }

            $uploadedPhotos = [];

            DB::transaction(function () use ($project, &$uploadedPhotos) {
                $maxSortOrder = $project->photos()->max('sort_order') ?? 0;

                foreach ($this->photos as $index => $photoData) {
                    $file = $photoData['file'];
                    $title = $photoData['title'] ?? null;
                    $isFeatured = $photoData['is_featured'] ?? false;

                    // If this is marked as featured, unmark other featured photos
                    if ($isFeatured) {
                        $project->photos()->update(['is_featured' => false]);
                    }

                    // Store the file locally first
                    $uuid = Str::uuid()->toString();
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'photo_' . time() . '_' . $index . '.' . $extension;
                    $filePath = $file->storeAs('projects/' . $project->uuid . '/photos', $fileName, 'public');

                    // Create photo record (stored locally on public disk)
                    $photo = $project->photos()->create([
                        'uuid' => $uuid,
                        'file_path' => $filePath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'title' => $title,
                        'is_featured' => $isFeatured,
                        'sort_order' => $maxSortOrder + $index + 1,
                        'uploaded_by' => $this->user->id,
                        'storage_disk' => 'public',
                    ]);

                    $uploadedPhotos[] = $photo;
                }
            });

            return response()->json([
                'success' => true,
                'message' => count($uploadedPhotos) . ' photo(s) uploaded successfully.',
                'data' => collect($uploadedPhotos)->map(fn ($photo) => [
                    'id' => $photo->id,
                    'uuid' => $photo->uuid,
                    'file_url' => $photo->file_url,
                    'file_name' => $photo->file_name,
                    'title' => $photo->title,
                    'is_featured' => $photo->is_featured,
                    'sort_order' => $photo->sort_order,
                ]),
            ], 201);

        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
