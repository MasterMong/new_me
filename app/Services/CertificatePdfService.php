<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificatePdfService
{
    /**
     * Render the certificate PDF (against the course's template if one is
     * configured, otherwise a plain fallback design), store it, and update
     * the certificate's pdf_url. Returns the public URL.
     */
    public function generate(Certificate $certificate): string
    {
        $template = $certificate->course->certificateTemplate;
        $dimensions = $template ? $this->imageDimensions($template) : null;

        $pdf = $template && $dimensions
            ? $this->renderWithTemplate($certificate, $template, $dimensions)
            : Pdf::loadView('pdf.certificate-fallback', ['certificate' => $certificate])
                ->setPaper('a4', 'landscape');

        $path = 'certificates/'.$certificate->certificate_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $url = Storage::disk('public')->url($path);
        $certificate->update(['pdf_url' => $url]);

        return $url;
    }

    protected function renderWithTemplate(Certificate $certificate, CertificateTemplate $template, array $dimensions)
    {
        [$width, $height] = $dimensions;

        // Points, not pixels, for the PDF page — assumes a 96 DPI source image
        // (px * 72/96 = px * 0.75) so the page's aspect ratio exactly matches
        // the template image and the picker's on-screen coordinates line up.
        $widthPt = $width * 0.75;
        $heightPt = $height * 0.75;

        return Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'imagePath' => $this->localPath($template->template_image_url),
            'namePercentX' => ($template->name_x / $width) * 100,
            'namePercentY' => ($template->name_y / $height) * 100,
            'datePercentX' => ($template->date_x / $width) * 100,
            'datePercentY' => ($template->date_y / $height) * 100,
        ])->setPaper([0, 0, $widthPt, $heightPt]);
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    protected function imageDimensions(CertificateTemplate $template): ?array
    {
        $path = $this->localPath($template->template_image_url);

        if (! is_file($path)) {
            return null;
        }

        $size = @getimagesize($path);

        if (! $size || $size[0] <= 0 || $size[1] <= 0) {
            return null;
        }

        return [$size[0], $size[1]];
    }

    /**
     * template_image_url is a public URL produced by Storage::disk('public')->url().
     * Resolve it back to a local filesystem path so dompdf/getimagesize don't
     * depend on an HTTP round-trip to the app's own storage symlink.
     */
    protected function localPath(string $url): string
    {
        $relative = Str::after($url, '/storage/');

        return Storage::disk('public')->path($relative);
    }
}
