<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'The Wedding of Wafii & Tasya' }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen text-stone-900 antialiased {{ ($portraitMode ?? false) ? 'portrait-mode' : '' }}">
  {{-- Ambient blurred background for desktop portrait mode sides --}}
  <div class="bg-blur-sides" aria-hidden="true"></div>

  {{-- Portrait frame: full-width on mobile, centered 480px column on desktop --}}
  <div id="portrait-frame">
    {{-- Decorative background blobs --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
      <div class="absolute -top-24 left-0 h-80 w-80 rounded-full bg-rose-200/30 blur-3xl"></div>
      <div class="absolute top-32 right-0 h-96 w-96 rounded-full bg-amber-200/30 blur-3xl"></div>
      <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-orange-100/50 blur-3xl"></div>
    </div>

  <header class="wedding-shell pt-5">
    <div class="glass-card flex flex-col gap-4 rounded-[28px] px-5 py-4 md:flex-row md:items-center md:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.38em] text-rose-900/60">Digital Wedding Invitation</p>
        <h1 class="title-display mt-1 text-2xl font-bold text-stone-900 md:text-3xl">Wafii & Tasya</h1>
      </div>
      <nav class="flex flex-wrap items-center gap-3 text-sm font-medium">
        <a href="{{ route('invitation.index') }}"
          class="rounded-full border border-rose-900/10 bg-white/80 px-4 py-2 text-rose-900 transition hover:bg-rose-900 hover:text-white">Undangan</a>
        <a href="{{ route('dashboard') }}"
          class="rounded-full border border-amber-900/10 bg-white/80 px-4 py-2 text-amber-900 transition hover:bg-amber-900 hover:text-white">Dashboard</a>
        @auth
          <form method="POST" action="{{ route('logout') }}" class="js-confirm-action"
            data-confirm-text="Yakin ingin logout?" data-submit-loading="Sedang logout...">
            @csrf
            <button class="rounded-full bg-stone-900 px-4 py-2 text-white transition hover:bg-stone-700">Keluar</button>
          </form>
        @else
          <a href="{{ route('login') }}"
            class="rounded-full bg-stone-900 px-4 py-2 text-white transition hover:bg-stone-700">Login</a>
        @endauth
      </nav>
    </div>
  </header>

  <main class="wedding-shell py-6 md:py-10">
    @yield('content')
  </main>

  @php
    $generatedInvitation = session('generatedInvitation');
    $dashboardGeneratedInvitation = session('dashboardGeneratedInvitation');
    $generatedGuestName =
        data_get($generatedInvitation, 'guest_name') ?: data_get($dashboardGeneratedInvitation, 'guest_name');
    $flashMessage = null;
    $flashIcon = null;

    if ($errors->any()) {
        $flashMessage = implode("\n", $errors->all());
        $flashIcon = 'error';
    } elseif (session('status')) {
        $flashMessage = (string) session('status');
        $flashIcon = 'success';
    } elseif ($generatedGuestName) {
        $flashMessage = 'Link tamu berhasil dibuat untuk ' . $generatedGuestName . '.';
        $flashIcon = 'success';
    } elseif (session('wishStatus')) {
        $flashMessage = (string) session('wishStatus');
        $flashIcon = 'success';
    }
  @endphp

  <script>
    (() => {
      const showLoading = (title = 'Memproses...') => {
        if (!window.Swal) {
          return;
        }

        Swal.fire({
          title,
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading(),
          showConfirmButton: false,
        });
      };

      document.querySelectorAll('form[data-swal-submit]').forEach((form) => {
        form.addEventListener('submit', (event) => {
          if (event.defaultPrevented) {
            return;
          }

          showLoading(form.dataset.swalSubmit || 'Memproses...');
        });
      });

      document.querySelectorAll('.js-confirm-action').forEach((form) => {
        form.addEventListener('submit', async (event) => {
          event.preventDefault();

          if (!window.Swal) {
            form.submit();
            return;
          }

          const result = await Swal.fire({
            icon: 'question',
            title: 'Konfirmasi',
            text: form.dataset.confirmText || 'Lanjutkan aksi ini?',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjut',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#7c2d12',
            cancelButtonColor: '#57534e',
          });

          if (!result.isConfirmed) {
            return;
          }

          showLoading(form.dataset.submitLoading || 'Memproses...');
          form.submit();
        });
      });

      const flashMessage = @json($flashMessage);
      const flashIcon = @json($flashIcon);

      if (!flashMessage || !window.Swal) {
        return;
      }

      Swal.fire({
        icon: flashIcon,
        title: flashIcon === 'error' ? 'Terjadi kesalahan' : 'Berhasil',
        text: flashMessage,
        confirmButtonColor: '#7c2d12',
      });
    })();
  </script>

  @stack('scripts')
  </div>{{-- /#portrait-frame --}}
</body>

</html>
