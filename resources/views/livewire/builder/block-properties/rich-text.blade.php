<div wire:ignore x-data="{
    debounceTimer: null,
    initEditor() {
        const quill = new Quill('#rich-text-editor-{{ $propertyName }}', {
            theme: 'snow',
            modules: {
                resize: {
                    tools: [
                        'left',
                        'center',
                        'right',
                        'full',
                        'edit',
                        {
                            text: 'Alt',
                            verify(activeEle) {
                                return activeEle && activeEle.tagName === 'IMG';
                            },
                            handler(evt, button, activeEle) {
                                let alt = activeEle.alt || '';
                                alt = window.prompt('Alt for image', alt);
                                if (alt == null) return;
                                activeEle.setAttribute('alt', alt);

                                $wire.set('currentValue', quill.getSemanticHTML());
                            },
                        },
                    ],
                },
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        quill.on('text-change', () => {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                $wire.set('currentValue', quill.getSemanticHTML());
            }, 500); // 500ms debounce
        });

    }
}" x-init="initEditor()">

    <div id="rich-text-editor-{{ $propertyName }}">{!! $currentValue !!}</div>

</div>
