@extends('layouts.public', ['title' => 'Undangan Wafii & Tasya', 'portraitMode' => true])

@section('content')
  @php
    $activeGuest = $guestInvitation ?? null;
    $generatedInvitation = session('generatedInvitation');
    $generatedShareLink = data_get($generatedInvitation, 'share_link');
    $shareText = session('generatedShareText');
  @endphp

  <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
    <div class="glass-card overflow-hidden rounded-[36px] p-6 sm:p-8 lg:p-10">
      <div
        class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-rose-900/70">
        The Wedding of</div>
      <h2 class="title-display mt-6 text-5xl leading-tight text-stone-950 sm:text-6xl">Wafii & Tasya</h2>
      <p class="serif-copy mt-4 max-w-2xl text-lg leading-8 text-stone-700 sm:text-xl">
        Dengan memohon rahmat dan ridha Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa
        restu pada hari bahagia kami.
      </p>

      <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="rounded-[28px] bg-stone-950 px-5 py-4 text-white shadow-lg shadow-stone-950/15">
          <p class="text-xs uppercase tracking-[0.3em] text-white/60">Akad Nikah</p>
          <p class="mt-2 text-lg font-semibold">Kamis, 11 Juni 2026</p>
          <p class="mt-1 text-sm text-white/80">Pukul 11.00 WIB</p>
        </div>
        <div class="rounded-[28px] border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm">
          <p class="text-xs uppercase tracking-[0.3em] text-amber-900/60">Lokasi</p>
          <p class="mt-2 text-lg font-semibold text-stone-900">Gedung Serbaguna</p>
          <p class="mt-1 text-sm text-stone-600">Kediaman keluarga mempelai</p>
        </div>
        <div class="rounded-[28px] border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
          <p class="text-xs uppercase tracking-[0.3em] text-rose-900/60">Undangan Tamu</p>
          <p class="mt-2 text-lg font-semibold text-stone-900">{{ number_format($generatedInvitation ? 1 : 0) }} dibuat
          </p>
          <p class="mt-1 text-sm text-stone-600">Data tamu langsung tersimpan ke database</p>
        </div>
      </div>

      @if ($activeGuest)
        <div class="mt-8 rounded-[30px] border border-amber-200 bg-white/90 p-6 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-900/60">Untuk tamu terhormat</p>
          <h3 class="title-display mt-2 text-3xl text-stone-950">{{ $activeGuest->guest_name }}</h3>
          <p class="mt-3 text-stone-700">{{ $activeGuest->share_message }}</p>
          <div class="mt-5 flex flex-wrap gap-3">
            <button type="button" data-copy-target="share-link"
              class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-700">Salin
              Link</button>
            <button type="button" data-copy-target="share-text"
              class="rounded-full border border-stone-300 bg-white px-5 py-3 text-sm font-semibold text-stone-900 transition hover:border-stone-500">Salin
              Teks</button>
          </div>
          <textarea id="share-text" class="sr-only">{{ $activeGuest->guest_name }}

{{ $activeGuest->share_message }}

Link undangan: {{ $activeGuest->share_link }}</textarea>
          <input id="share-link" class="sr-only" value="{{ $activeGuest->share_link }}">
        </div>
      @endif
    </div>

    <div class="space-y-6">
      <div class="glass-card rounded-[34px] p-6 sm:p-7">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Menu Client</p>
        <h3 class="title-display mt-2 text-3xl text-stone-950">Buat nama tamu undangan</h3>
        <p class="mt-2 text-sm leading-6 text-stone-600">Nama yang dimasukkan akan tersimpan sebagai data tamu dan link
          khusus dapat dibagikan ke orang yang diundang.</p>

        <form class="mt-6 space-y-4" method="POST" action="{{ route('invitation.store') }}">
          @csrf
          <div>
            <label class="mb-2 block text-sm font-semibold text-stone-700">Nama tamu</label>
            <input name="guest_name" value="{{ old('guest_name') }}"
              class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
              placeholder="Contoh: Bapak Ahmad dan keluarga">
          </div>
          <div>
            <label class="mb-2 block text-sm font-semibold text-stone-700">Kata-kata untuk dibagikan</label>
            <textarea name="share_message" rows="4"
              class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
              placeholder="Tulis kalimat yang ingin disertakan saat mengirim undangan">{{ old('share_message', 'Kami dengan hormat mengundang Anda untuk hadir dan memberikan doa restu.') }}</textarea>
          </div>
          <button
            class="w-full rounded-2xl bg-linear-to-r from-stone-950 to-rose-900 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-stone-950/15 transition hover:-translate-y-px">Simpan
            Tamu & Buat Link</button>
        </form>

        @if ($generatedInvitation && $shareText)
          <div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-900/60">Link siap dibagikan</p>
            <p class="mt-2 break-all text-sm text-emerald-950">{{ $generatedShareLink }}</p>
            <div class="mt-4 flex gap-3">
              <button type="button" data-copy-target="generated-link"
                class="rounded-full bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Copy Link</button>
              <button type="button" data-copy-target="generated-share"
                class="rounded-full border border-emerald-900/15 bg-white px-4 py-2 text-sm font-semibold text-emerald-900">Copy
                Teks</button>
            </div>
            <textarea id="generated-share" class="sr-only">{{ $shareText }}</textarea>
            <input id="generated-link" class="sr-only" value="{{ $generatedShareLink }}">
          </div>
        @endif
      </div>

      <div class="glass-card rounded-[34px] p-6 sm:p-7">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Ucapan & Doa</p>
        <h3 class="title-display mt-2 text-3xl text-stone-950">Pesan untuk pengantin</h3>

        <form class="mt-6 space-y-4" method="POST" action="{{ route('wishes.store') }}">
          @csrf
          <input type="hidden" name="guest_invitation_code" value="{{ $activeGuest?->share_code }}">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-semibold text-stone-700">Nama Anda</label>
              <input name="guest_name" value="{{ old('guest_name') }}"
                class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none transition focus:border-rose-500 focus:ring-4 focus:ring-rose-100"
                placeholder="Nama pengirim ucapan">
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-stone-700">Konfirmasi</label>
              <select name="attendance_status"
                class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none transition focus:border-rose-500 focus:ring-4 focus:ring-rose-100">
                <option value="">Pilih</option>
                <option value="hadir" @selected(old('attendance_status') === 'hadir')>Hadir</option>
                <option value="tidak_hadir" @selected(old('attendance_status') === 'tidak_hadir')>Tidak Hadir</option>
              </select>
            </div>
          </div>
          <div>
            <label class="mb-2 block text-sm font-semibold text-stone-700">Ucapan</label>
            <textarea name="message" rows="4"
              class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none transition focus:border-rose-500 focus:ring-4 focus:ring-rose-100"
              placeholder="Tulis doa dan ucapan terbaik Anda"></textarea>
          </div>
          <button
            class="w-full rounded-2xl bg-rose-900 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-rose-900/15 transition hover:bg-rose-800">Kirim
            Ucapan</button>
        </form>
      </div>
    </div>
  </section>

  <section class="mt-6 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
    <div class="glass-card rounded-[34px] p-6 sm:p-7">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Ringkasan</p>
      <h3 class="title-display mt-2 text-3xl text-stone-950">Recent Wishes</h3>
      <div class="mt-5 space-y-4">
        @php
          $attendanceLabels = [
              'hadir' => 'Hadir',
              'tidak_hadir' => 'Tidak Hadir',
              'berhalangan' => 'Tidak Hadir',
              'ragu' => 'Tidak Hadir',
          ];
        @endphp
        @forelse ($recentWishes as $wish)
          <article class="rounded-3xl border border-stone-200 bg-white/90 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold text-stone-900">{{ $wish->guest_name }}</p>
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">
                  {{ $attendanceLabels[$wish->attendance_status] ?? $wish->attendance_status }}</p>
              </div>
              <span
                class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-900">{{ $wish->created_at?->diffForHumans() }}</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-stone-600">{{ $wish->message }}</p>
          </article>
        @empty
          <p class="rounded-3xl border border-dashed border-stone-300 bg-white/70 p-6 text-sm text-stone-500">Belum
            ada ucapan tersimpan.</p>
        @endforelse
      </div>
    </div>

    <div class="glass-card rounded-[34px] p-6 sm:p-7">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Informasi Acara</p>
      <h3 class="title-display mt-2 text-3xl text-stone-950">Jadwal pernikahan</h3>

      <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-3xl bg-stone-950 p-5 text-white">
          <p class="text-xs uppercase tracking-[0.3em] text-white/60">Akad</p>
          <p class="mt-2 text-xl font-semibold">11 Juni 2026</p>
          <p class="text-sm text-white/75">11.00 WIB - selesai</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
          <p class="text-xs uppercase tracking-[0.3em] text-amber-900/60">Resepsi</p>
          <p class="mt-2 text-xl font-semibold text-stone-900">11 Juni 2026</p>
          <p class="text-sm text-stone-600">13.00 WIB - selesai</p>
        </div>
      </div>

      <div class="mt-6 rounded-[28px] border border-rose-200 bg-white/85 p-5">
        <p class="title-display text-2xl text-stone-950">"Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan
          untukmu pasangan hidup dari jenismu sendiri."</p>
        <p class="mt-3 text-sm uppercase tracking-[0.3em] text-stone-500">QS. Ar-Rum: 21</p>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    document.querySelectorAll('[data-copy-target]').forEach((button) => {
      button.addEventListener('click', async () => {
        const targetId = button.getAttribute('data-copy-target');
        const target = document.getElementById(targetId);

        if (!target) {
          return;
        }

        try {
          await navigator.clipboard.writeText(target.value || target.textContent || '');
          const originalLabel = button.textContent;
          button.textContent = 'Tersalin';
          setTimeout(() => {
            button.textContent = originalLabel;
          }, 1500);
        } catch (error) {
          console.error(error);
        }
      });
    });
  </script>
@endpush
