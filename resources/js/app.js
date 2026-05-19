import Quill from 'quill';
import 'quill/dist/quill.snow.css';

document.addEventListener('alpine:init', () => {
    Alpine.data('richEditor', ({ model, initialValue, placeholder }) => ({
        editor: null,

        init() {
            this.editor = new Quill(this.$refs.quill, {
                theme: 'snow',
                placeholder: placeholder || '',
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'clean'],
                    ],
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
    }));
});
