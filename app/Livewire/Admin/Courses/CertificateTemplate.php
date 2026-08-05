<?php

namespace App\Livewire\Admin\Courses;

use App\Models\CertificateTemplate as CertificateTemplateModel;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CertificateTemplate extends Component
{
    use WithFileUploads;

    public Course $course;

    public $templateImage;

    public string $templateImageUrl = '';

    public int $nameX = 0;

    public int $nameY = 0;

    public int $dateX = 0;

    public int $dateY = 0;

    public function mount(Course $course): void
    {
        $this->course = $course;

        $template = $course->certificateTemplate;

        if ($template) {
            $this->templateImageUrl = $template->template_image_url;
            $this->nameX = $template->name_x;
            $this->nameY = $template->name_y;
            $this->dateX = $template->date_x;
            $this->dateY = $template->date_y;
        }
    }

    protected function rules(): array
    {
        return [
            'templateImage' => ['nullable', 'image', 'max:5120'],
            'nameX' => ['required', 'integer', 'min:0'],
            'nameY' => ['required', 'integer', 'min:0'],
            'dateX' => ['required', 'integer', 'min:0'],
            'dateY' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'templateImage.image' => 'กรุณาอัปโหลดไฟล์รูปภาพ',
            'templateImage.max' => 'ขนาดไฟล์ต้องไม่เกิน 5MB',
        ];
    }

    public function save(): void
    {
        $this->validate();

        if (! $this->templateImage && ! $this->templateImageUrl) {
            $this->addError('templateImage', 'กรุณาอัปโหลดภาพเทมเพลตเกียรติบัตร');

            return;
        }

        $imageUrl = $this->templateImageUrl;
        if ($this->templateImage) {
            $path = $this->templateImage->store('certificates/templates', 'public');
            $imageUrl = Storage::disk('public')->url($path);
        }

        CertificateTemplateModel::updateOrCreate(
            ['course_id' => $this->course->id],
            [
                'template_image_url' => $imageUrl,
                'name_x' => $this->nameX,
                'name_y' => $this->nameY,
                'date_x' => $this->dateX,
                'date_y' => $this->dateY,
            ]
        );

        $this->templateImageUrl = $imageUrl;
        $this->templateImage = null;

        session()->flash('status', 'บันทึกเทมเพลตเกียรติบัตรเรียบร้อยแล้ว');
    }

    public function render()
    {
        return view('livewire.admin.courses.certificate-template')
            ->layout('layouts.app', ['title' => 'เทมเพลตเกียรติบัตร: '.$this->course->title]);
    }
}
