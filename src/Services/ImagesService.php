<?php

namespace Services;

use Exception;
use Interfaces\IImagesRepository;
use Result;

enum E_IMAGES_TYPES {
    case Profile;
    case Post;
}

class ImagesService {
    public function __construct(public IImagesRepository $imageRepo) {
    }

    public static function getServerImagesDirByType(E_IMAGES_TYPES $imageType): ?string {
        if($imageType == E_IMAGES_TYPES::Profile) {
            return __DIR__ . '/../../public/storage/uploads/profile_images/';
        }
        else if($imageType == E_IMAGES_TYPES::Post) {
            return __DIR__ . '/../../public/storage/uploads/post_images/';
        }
        else {
            throw new Exception("Upload type didn't recognised");
        }
    }

    public static function getWebDirByType(E_IMAGES_TYPES $imageType): ?string {
        if($imageType == E_IMAGES_TYPES::Profile) {
            return '/storage/uploads/profile_images/';
        }
        else if($imageType == E_IMAGES_TYPES::Post) {
            return '/storage/uploads/post_images/';
        }
        else {
            throw new Exception("Image type didn't recognised");
        }
    }

    public static function deleteImage(E_IMAGES_TYPES $uploadType, string $imageName): void {
        $uploadDir = ImagesService::getServerImagesDirByType($uploadType);
        @unlink($uploadDir . $imageName);
    }

    public static function deleteImages(E_IMAGES_TYPES $uploadType, array $fileNames): void {
        $uploadDir = ImagesService::getServerImagesDirByType($uploadType);

        foreach($fileNames as $fileName) {
            @unlink($uploadDir . $fileName);
        }
    }

    public static function uploadNewImage(E_IMAGES_TYPES $uploadType, string $from, string $fileType): Result {
        $uploadDir = ImagesService::getServerImagesDirByType($uploadType);
        $fileName = generateRandomToken() . '.' . $fileType;
        $targetFilePath = $uploadDir . $fileName;

        if(!move_uploaded_file($from, $targetFilePath)) {
            return Result::fail("Failed to upload image");
        }
        
        return Result::success("Image uploaded succesfully", $fileName);
    }

    public function addMultiplePostImages(int $postId, array $names): int {
        if(empty($names)) {
            return 0;
        }

        error_log("lolsdf");

        return $this->imageRepo->createMultiplePostImages($postId, $names);
    }

    public function getPostImages(int $postId): array {
        return $this->imageRepo->findPostImages($postId);
    }

    public static function isImageTypeSupported(string $type): bool {
        return strtolower($type) == "jpg" || strtolower($type) == "png";
    }

    public static function getImageTypeSupportingError(): string {
        return "You need to upload jpg or png photos";
    }
}