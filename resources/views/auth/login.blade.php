<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Dashboard Wedding</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen text-stone-900 antialiased">
  <div class="fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute -top-24 left-0 h-80 w-80 rounded-full bg-rose-200/30 blur-3xl"></div>
    <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-amber-200/30 blur-3xl"></div>
  </div>

  <main class="wedding-shell flex min-h-screen items-center py-10">
    <section class="glass-card mx-auto w-full max-w-2xl rounded-[36px] p-8 sm:p-10">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Dashboard Access</p>
      <div class="mt-4">
        <h2 class="title-display text-3xl text-stone-950 sm:text-4xl">Masuk Dashboard</h2>
        <p class="mt-2 text-sm text-stone-600">Gunakan akun admin yang sudah disiapkan di database.</p>

        <form id="login-form" class="mt-6 space-y-4" method="POST" action="{{ route('login.store') }}">
          @csrf
          <div>
            <label class="mb-2 block text-sm font-semibold text-stone-700">Email</label>
            <input id="login-email" type="email" name="email" value="{{ old('email') }}" required maxlength="120"
              class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
              placeholder="admin@wedding.test">
          </div>
          <div>
            <label class="mb-2 block text-sm font-semibold text-stone-700">Password</label>
            <input id="login-password" type="password" name="password" required minlength="6" maxlength="120"
              class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
              placeholder="password">
          </div>
          <label class="flex items-center gap-3 text-sm text-stone-600">
            <input type="checkbox" name="remember"
              class="h-4 w-4 rounded border-stone-300 text-rose-900 focus:ring-rose-900">
            Ingat saya
          </label>
          <button
            class="w-full rounded-2xl bg-stone-950 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-stone-800">Masuk
            ke Dashboard</button>
        </form>

        <div class="mt-6 rounded-[28px] border border-amber-200 bg-amber-50 p-5 text-sm text-stone-700">
          <p class="font-semibold text-stone-950">Akses awal yang disarankan</p>
          <p class="mt-2">Email: admin@wedding.test</p>
          <p>Password: password</p>
        </div>
      </div>
    </section>
  </main>

  @if ($errors->any())
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Login gagal',
        text: @json($errors->first()),
        confirmButtonColor: '#7c2d12',
      });
    </script>
  @endif

  @if (session('status'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: @json(session('status')),
        confirmButtonColor: '#7c2d12',
      });
    </script>
  @endif

  <script>
    (() => {
      const loginForm = document.getElementById('login-form');
      const emailInput = document.getElementById('login-email');
      const passwordInput = document.getElementById('login-password');

      if (!loginForm || !window.Swal) {
        return;
      }

      loginForm.addEventListener('submit', (event) => {
        if (emailInput) {
          emailInput.value = (emailInput.value || '').trim();
        }

        if (!loginForm.checkValidity() || !(emailInput?.value || '').length || !(passwordInput?.value || '').trim()
          .length) {
          event.preventDefault();
          loginForm.reportValidity();
          Swal.fire({
            icon: 'warning',
            title: 'Input belum lengkap',
            text: 'Mohon isi email dan password dengan benar sebelum login.',
            confirmButtonColor: '#7c2d12',
          });
          return;
        }

        Swal.fire({
          title: 'Sedang login...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading(),
          showConfirmButton: false,
        });
      });
    })();
  </script>
</body>

</html>
