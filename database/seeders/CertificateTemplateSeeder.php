<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $course1 = Course::where('duration_hours', 6)->first();

        $path = 'certificates/templates/course-'.$course1->id.'.jpg';
        $width = 1600;
        $height = 1131; // A4 landscape aspect ratio

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->renderPlaceholder($width, $height));
        }

        CertificateTemplate::updateOrCreate(
            ['course_id' => $course1->id],
            [
                'template_image_url' => Storage::disk('public')->url($path),
                'name_x' => (int) ($width * 0.5),
                'name_y' => (int) ($height * 0.48),
                'date_x' => (int) ($width * 0.5),
                'date_y' => (int) ($height * 0.68),
            ]
        );
    }

    private function renderPlaceholder(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        $background = imagecolorallocate($image, 245, 243, 232);
        $border = imagecolorallocate($image, 0, 62, 116);
        $gold = imagecolorallocate($image, 116, 91, 0);

        imagefill($image, 0, 0, $background);
        imagerectangle($image, 20, 20, $width - 21, $height - 21, $border);
        imagerectangle($image, 32, 32, $width - 33, $height - 33, $gold);

        ob_start();
        imagejpeg($image, null, 90);
        $data = ob_get_clean();
        imagedestroy($image);

        return $data;
    }
}
