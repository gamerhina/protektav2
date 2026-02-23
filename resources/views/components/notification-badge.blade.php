@auth('admin')
    @php
        $unreadNotifications = auth('admin')->user()->unreadNotifications->count();
        $notifications = auth('admin')->user()->notifications()->orderBy('created_at', 'desc')->limit(5)->get();
    @endphp
    <div class="relative">
        <button class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none" onclick="toggleNotifications()">
            <i class="fas fa-bell text-lg"></i>
            @if($unreadNotifications > 0)
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full transform -translate-y-1/2 translate-x-1/2">
                    {{ $unreadNotifications }}
                </span>
            @endif
        </button>
        
        <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
                @if($notifications->count() > 0)
                    @foreach($notifications as $notification)
                        <a href="{{ $notification->data['action_url'] ?? '#' }}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 @if(!$notification->read_at) bg-blue-50 @endif">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    @if($notification->type === 'App\Notifications\SuratSubmittedNotification')
                                        <i class="fas fa-file-alt text-blue-500 mt-0.5"></i>
                                    @elseif($notification->type === 'App\Notifications\SuratStatusUpdatedNotification')
                                        <i class="fas fa-sync-alt text-green-500 mt-0.5"></i>
                                    @else
                                        <i class="fas fa-info-circle text-gray-500 mt-0.5"></i>
                                    @endif
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm text-gray-900">
                                        {{ $notification->data['message'] ?? $notification->data['subject'] ?? 'Notifikasi' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @if(!$notification->read_at)
                                    <div class="flex-shrink-0 ml-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="px-4 py-8 text-center text-sm text-gray-500">
                        Belum ada notifikasi
                    </div>
                @endif
            </div>
            <div class="px-4 py-2 border-t border-gray-200">
                <a href="{{ route('admin.notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Lihat semua notifikasi
                </a>
            </div>
        </div>
    </div>
@endauth

@auth('dosen')
    @php
        $unreadNotifications = auth('dosen')->user()->unreadNotifications->count();
        $notifications = auth('dosen')->user()->notifications()->orderBy('created_at', 'desc')->limit(5)->get();
    @endphp
    <div class="relative">
        <button class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none" onclick="toggleNotifications()">
            <i class="fas fa-bell text-lg"></i>
            @if($unreadNotifications > 0)
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full transform -translate-y-1/2 translate-x-1/2">
                    {{ $unreadNotifications }}
                </span>
            @endif
        </button>
        
        <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
                @if($notifications->count() > 0)
                    @foreach($notifications as $notification)
                        <a href="{{ $notification->data['action_url'] ?? '#' }}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 @if(!$notification->read_at) bg-blue-50 @endif">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    @if($notification->type === 'App\Notifications\SuratStatusUpdatedNotification')
                                        <i class="fas fa-sync-alt text-green-500 mt-0.5"></i>
                                    @else
                                        <i class="fas fa-info-circle text-gray-500 mt-0.5"></i>
                                    @endif
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm text-gray-900">
                                        {{ $notification->data['message'] ?? $notification->data['subject'] ?? 'Notifikasi' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @if(!$notification->read_at)
                                    <div class="flex-shrink-0 ml-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="px-4 py-8 text-center text-sm text-gray-500">
                        Belum ada notifikasi
                    </div>
                @endif
            </div>
        </div>
    </div>
@endauth

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notification-dropdown');
    dropdown.classList.toggle('hidden');
    
    // Close dropdown when clicking outside
    if (!dropdown.classList.contains('hidden')) {
        setTimeout(() => {
            document.addEventListener('click', closeNotifications);
        }, 100);
    }
}

function closeNotifications(event) {
    const dropdown = document.getElementById('notification-dropdown');
    if (!dropdown.contains(event.target) && !event.target.closest('button[onclick="toggleNotifications()"]')) {
        dropdown.classList.add('hidden');
        document.removeEventListener('click', closeNotifications);
    }
}

// Mark notifications as read when clicked
document.addEventListener('DOMContentLoaded', function() {
    @auth('admin')
        const markAsReadUrl = '{{ route("admin.notifications.markAsRead") }}';
        fetch(markAsReadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    @endauth
    
    @auth('dosen')
        const markAsReadUrl = '{{ route("dosen.notifications.markAsRead") }}';
        fetch(markAsReadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    @endauth
});
</script>
