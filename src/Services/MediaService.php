<?php

namespace ppeCore\dvtinh\Services;

use App\Models\Media;
use Image;
use Imagick;

class MediaService
{

    public function map_medium($mediaCol, $dataIds, $data)
    {
        $media = Media::whereIn($mediaCol, $dataIds)
            ->get()
            ->keyBy($mediaCol)
            ->toArray();
        $data = $data->map(function ($datum) use ($media) {
            $medium = @$media[$datum['id']];
            if (isset($medium['file'])) {
                $image = asset('storage/' . $medium['file']);
                $medium['image'] = $image;
                $medium['thumb_image'] = self::get_thumb($image);
                $datum->medium = $medium;
                return $datum;
            } else {
                $datum->medium = null;
                return $datum;
            }
        });
        return $data;
    }

    public function get_thumb($imagePath)
    {
        $imagePath = str_replace('/images/', '/thumb-images/', $imagePath);
        $imagePath = str_replace('.wepb', '.jpg', $imagePath);
        return $imagePath;
    }

    public function thumb_image($path, $imagePath)
    {
        $imagePath = self::get_thumb($imagePath);
        return Image::make($path)
            ->fit(90, 90)
            ->save($imagePath, 100);
    }

    public function jcphp01_generate_webp_image($file, $outputFile, $compression_quality = 80)
    {
        if (!file_exists($file)) {
            return false;
        }
        if (file_exists($outputFile)) {
            return $outputFile;
        }
        $file_type = strtolower(mime_content_type($file));
        $file_type = str_replace('image/', '', $file_type);

        if (function_exists('imagewebp')) {
            switch ($file_type) {
                case 'jpeg':
                case 'jpg':
                    $image = imagecreatefromjpeg($file);
                    break;
                case 'png':
                    $image = imagecreatefrompng($file);
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($file);
                    break;
                default:
                    return false;
            }
            $result = imagewebp($image, $outputFile, $compression_quality);
            if (false === $result) {
                return false;
            }
            imagedestroy($image);
            return $outputFile;
        } elseif (class_exists('Imagick')) {
            $image = new Imagick();
            $image->readImage($file);
            if ($file_type === 'png') {
                $image->setImageFormat('webp');
                $image->setImageCompressionQuality($compression_quality);
                $image->setOption('webp:lossless', 'true');
            }
            $image->writeImage($outputFile);
            return $outputFile;
        }
        return false;
    }

}

