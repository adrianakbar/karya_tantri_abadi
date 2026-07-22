<div class="space-y-4">
    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
        Perbandingan Perubahan Data
    </div>
    
    @if(empty($oldValues) && empty($newValues))
        <div class="text-center text-gray-500 dark:text-gray-400 py-8">
            Tidak ada data perubahan yang tersedia
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Old Values -->
            <div class="space-y-2">
                <h3 class="text-sm font-medium text-red-600 dark:text-red-400 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                    Nilai Sebelumnya
                </h3>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-200 dark:border-red-800">
                    @if(empty($oldValues))
                        <div class="text-sm text-gray-500 dark:text-gray-400 italic">Tidak ada data (record baru)</div>
                    @else
                        @foreach($oldValues as $key => $value)
                            <div class="flex justify-between items-start py-1 border-b border-red-200 dark:border-red-700 last:border-0">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-100 ml-2 break-all">
                                    @if(is_null($value))
                                        <em class="text-gray-500">null</em>
                                    @elseif(is_bool($value))
                                        {{ $value ? 'true' : 'false' }}
                                    @elseif(is_array($value) || is_object($value))
                                        <pre class="text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        {{ $value }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- New Values -->
            <div class="space-y-2">
                <h3 class="text-sm font-medium text-green-600 dark:text-green-400 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nilai Sesudahnya
                </h3>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-200 dark:border-green-800">
                    @if(empty($newValues))
                        <div class="text-sm text-gray-500 dark:text-gray-400 italic">Tidak ada data (record dihapus)</div>
                    @else
                        @foreach($newValues as $key => $value)
                            <div class="flex justify-between items-start py-1 border-b border-green-200 dark:border-green-700 last:border-0">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-100 ml-2 break-all">
                                    @if(is_null($value))
                                        <em class="text-gray-500">null</em>
                                    @elseif(is_bool($value))
                                        {{ $value ? 'true' : 'false' }}
                                    @elseif(is_array($value) || is_object($value))
                                        <pre class="text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        {{ $value }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary of Changes -->
        @if(!empty($oldValues) && !empty($newValues))
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Ringkasan Perubahan:</h4>
                <div class="space-y-1">
                    @foreach($newValues as $key => $newValue)
                        @if(array_key_exists($key, $oldValues))
                            @php
                                $oldValue = $oldValues[$key];
                                $hasChanged = $oldValue !== $newValue;
                            @endphp
                            @if($hasChanged)
                                <div class="text-xs text-blue-700 dark:text-blue-300">
                                    <span class="font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}</span>: 
                                    <span class="text-red-600 dark:text-red-400">"{{ $oldValue }}"</span> 
                                    → 
                                    <span class="text-green-600 dark:text-green-400">"{{ $newValue }}"</span>
                                </div>
                            @endif
                        @else
                            <div class="text-xs text-blue-700 dark:text-blue-300">
                                <span class="font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}</span>: 
                                <span class="text-green-600 dark:text-green-400">Ditambahkan: "{{ $newValue }}"</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
