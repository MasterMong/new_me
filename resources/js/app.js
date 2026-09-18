import Quill from 'quill';
import 'quill/dist/quill.snow.css';

document.addEventListener('alpine:init', () => {
    Alpine.data('richEditor', ({ model, initialValue, placeholder, enableImages }) => ({
        editor: null,
        pendingImageIndex: null,

        init() {
            const toolbar = [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'clean'],
            ];

            if (enableImages) {
                toolbar[toolbar.length - 1].unshift('image');
            }

            this.editor = new Quill(this.$refs.quill, {
                theme: 'snow',
                placeholder: placeholder || '',
                modules: {
                    toolbar: enableImages
                        ? { container: toolbar, handlers: { image: () => this.pickImage() } }
                        : toolbar,
                },
            });

            this.editor.root.innerHTML = initialValue || '';

            this.editor.on('text-change', () => {
                const html = this.editor.root.innerHTML;
                this.$wire.set(model, html === '<p><br></p>' ? '' : html);
            });

            // Sync when Livewire updates the property (e.g. modal opens with different data)
            this.$wire.$watch(model, (newValue) => {
                if (newValue !== this.editor.root.innerHTML) {
                    this.editor.root.innerHTML = newValue || '';
                }
            });
        },

        // Remembers where the cursor was, then hands off to the hidden
        // Livewire-bound file input — the round trip to store the upload
        // is async, so the insertion position has to survive it.
        pickImage() {
            this.pendingImageIndex = this.editor.getSelection(true)?.index ?? this.editor.getLength();
            this.$refs.imageInput.click();
        },

        onImageUploaded(url) {
            const index = this.pendingImageIndex ?? this.editor.getLength();
            this.editor.insertEmbed(index, 'image', url, 'user');
            this.editor.setSelection(index + 1);
            this.pendingImageIndex = null;
        },
    }));
});
