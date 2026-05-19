<div class="p-6 max-w-3xl mx-auto">
    <div class="text-center mb-10">
        <div class="size-16 rounded-full bg-secondary/10 flex items-center justify-center text-secondary mx-auto mb-4">
            <span class="material-symbols-outlined text-4xl">rate_review</span>
        </div>
        <flux:heading size="xl" class="mb-2">แบบประเมินความพึงพอใจ</flux:heading>
        <flux:subheading>กรุณาประเมินหลักสูตร "{{ $course->title }}" เพื่อรับเกียรติบัตร</flux:subheading>
    </div>

    <div class="bg-white dark:bg-zinc-900 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <form wire:submit="submitReview" class="space-y-8">
            {{-- Rating Stars --}}
            <div class="text-center">
                <flux:heading size="lg" class="mb-4">คุณมีความพึงพอใจต่อหลักสูตรนี้ในระดับใด?</flux:heading>
                <div class="flex items-center justify-center gap-2">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" wire:click="setRating({{ $i }})" class="transition-transform hover:scale-110 focus:outline-none">
                            <span class="material-symbols-outlined text-5xl {{ $i <= $rating ? 'text-secondary' : 'text-zinc-200 dark:text-zinc-700' }}" style="font-variation-settings: 'FILL' {{ $i <= $rating ? '1' : '0' }};">star</span>
                        </button>
                    @endfor
                </div>
                <div class="flex justify-center gap-10 mt-2 text-sm text-zinc-500">
                    <span>(น้อยที่สุด)</span>
                    <span>(มากที่สุด)</span>
                </div>
                @error('rating') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
            </div>

            <hr class="border-zinc-100 dark:border-zinc-800">

            {{-- Comment --}}
            <div>
                <flux:heading size="lg" class="mb-4">ข้อเสนอแนะเพิ่มเติม (ถ้ามี)</flux:heading>
                <flux:textarea wire:model="comment" rows="4" placeholder="พิมพ์ข้อเสนอแนะหรือความคิดเห็นของคุณเกี่ยวกับหลักสูตรนี้..." />
            </div>

            <div class="flex justify-end pt-4">
                <flux:button type="submit" variant="primary" class="px-8">ส่งแบบประเมิน</flux:button>
            </div>
        </form>
    </div>
</div>
