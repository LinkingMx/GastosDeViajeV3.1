<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="fi-fo-placeholder">
        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300">
            {{ $value }}
        </span>
    </div>
</x-dynamic-component>
