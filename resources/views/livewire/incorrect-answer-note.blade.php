<div>
    {{-- Do your work, then step back. --}}
    <div class="flex">
        <div class="w-1/2 p-4">
            <img src="{{ asset($problem->image_path) }}" alt="Problem Image" class="w-full">
            <p class="mt-4">{{ $problem->description }}</p>
        </div>
        <div class="w-1/2 p-4">
            <form wire:submit.prevent="save">
                <div wire:ignore>
                    <textarea id="editor" wire:model="content"></textarea>
                </div>
                <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">저장</button>
            </form>
        </div>
    </div>

    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            ClassicEditor
                .create(document.querySelector('#editor'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                    @this.set('content', editor.getData());
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
</div>
