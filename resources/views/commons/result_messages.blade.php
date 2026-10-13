{{-- 共通トースト通知 --}}
@if (session('success') || session('error') || $errors->any())
    <div id="flash-toast" class="toast toast-end toast-top z-50 mt-16">

        @if (session('success'))
            <div role="status" class="alert alert-success shadow-lg">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="alert alert-error shadow-lg">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @foreach ($errors->all() as $error)
            <div role="alert" class="alert alert-error shadow-lg">
                <span>{{ $error }}</span>
            </div>
        @endforeach

    </div>

    <script>
        setTimeout(function() {
            const toast = document.getElementById('flash-toast');

            if (toast) {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');

                setTimeout(function() {
                    toast.remove();
                }, 500);
            }
        }, 4000);
    </script>
@endif
