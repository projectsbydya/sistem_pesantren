@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 focus:border-emerald-500 dark:focus:border-emerald-500 focus:ring-emerald-500 dark:focus:ring-emerald-500/20 rounded-md shadow-sm transition-colors']) }}>
