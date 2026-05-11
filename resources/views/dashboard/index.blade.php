@extends('layouts.public', ['title' => 'Dashboard Undangan'])

@section('content')
  @php
    $generatedInvitation = session('dashboardGeneratedInvitation');
    $generatedShareText = session('dashboardGeneratedShareText');
    $generatedShareLink = data_get($generatedInvitation, 'share_link');
  @endphp

  <section class="grid gap-6 lg:grid-cols-[1.08fr_0.92fr]">
    <div class="glass-card rounded-4xl p-5 sm:p-7 lg:p-8">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Dashboard Admin</p>
      <h2 class="title-display mt-3 text-3xl text-stone-950 sm:text-4xl lg:text-5xl">Pencatatan tamu & statistik pengunjung
      </h2>

      <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl bg-stone-950 p-4 text-white sm:p-5">
          <p class="text-[11px] uppercase tracking-[0.3em] text-white/55">Tamu dibuat</p>
          <p class="mt-2 text-2xl font-semibold sm:text-3xl">{{ number_format($totalInvitations) }}</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 sm:p-5">
          <p class="text-[11px] uppercase tracking-[0.3em] text-amber-900/60">Total kunjungan</p>
          <p class="mt-2 text-2xl font-semibold text-stone-950 sm:text-3xl">{{ number_format($totalVisits) }}</p>
        </div>
        <div class="rounded-3xl border border-rose-200 bg-rose-50 p-4 sm:p-5">
          <p class="text-[11px] uppercase tracking-[0.3em] text-rose-900/60">Ucapan tersimpan</p>
          <p class="mt-2 text-2xl font-semibold text-stone-950 sm:text-3xl">{{ number_format($totalWishes) }}</p>
        </div>
        <div class="rounded-3xl border border-stone-200 bg-white p-4 sm:p-5">
          <p class="text-[11px] uppercase tracking-[0.3em] text-stone-500">Hari ini</p>
          <p class="mt-2 text-2xl font-semibold text-stone-950 sm:text-3xl">{{ number_format($todayVisits) }}</p>
        </div>
      </div>
    </div>

    <div class="glass-card rounded-4xl p-5 sm:p-7 lg:p-8">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Statistik 7 hari</p>
      <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <h3 class="title-display text-2xl text-stone-950 sm:text-3xl">Visitor trend</h3>
        <p class="text-sm text-stone-500">Pergerakan kunjungan dalam seminggu terakhir</p>
      </div>
      <div class="mt-6 flex h-72 items-end gap-2 sm:gap-3">
        @foreach ($visitorTrend as $item)
          @php
            $height = $peakVisitorCount > 0 ? max(10, (int) round(($item['count'] / $peakVisitorCount) * 100)) : 10;
          @endphp
          <div class="flex flex-1 flex-col items-center gap-3">
            <div class="flex h-52 w-full items-end rounded-3xl bg-white/60 p-2 shadow-inner sm:h-56">
              <div class="w-full rounded-[18px] bg-linear-to-t from-rose-900 to-amber-400 transition-all duration-300"
                style="height: {{ $height }}%"></div>
            </div>
            <div class="text-center text-[10px] font-semibold uppercase tracking-[0.18em] text-stone-500 sm:text-xs">
              <div>{{ $item['label'] }}</div>
              <div class="mt-1 text-sm text-stone-900">{{ $item['count'] }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <section id="menu-client" class="mt-6 grid gap-6 scroll-mt-24 lg:grid-cols-[1.05fr_0.95fr]">
    <div class="glass-card rounded-4xl p-5 sm:p-7 lg:p-8">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Menu Client</p>
      <h3 class="title-display mt-2 text-2xl text-stone-950 sm:text-3xl">Tambah tamu undangan</h3>

      <form class="mt-6 space-y-4" method="POST" action="{{ route('dashboard.invitation.store') }}"
        data-swal-submit="Membuat undangan...">
        @csrf
        <div>
          <label class="mb-2 block text-sm font-semibold text-stone-700">Nama tamu</label>
          <input name="guest_name" value="{{ old('guest_name') }}" required minlength="2" maxlength="120"
            pattern=".*\S.*"
            class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100 sm:text-base"
            placeholder="Contoh: Bapak Ahmad dan keluarga">
        </div>
        <button
          class="w-full rounded-2xl bg-linear-to-r from-stone-950 to-rose-900 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-stone-950/15 transition hover:-translate-y-px">Simpan
          Tamu & Buat Link</button>
      </form>

      @if ($generatedInvitation && $generatedShareText)
        <div class="mt-5 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5">
          <p class="text-sm text-emerald-900">Undangan untuk <span
              class="font-semibold">{{ data_get($generatedInvitation, 'guest_name') }}</span> berhasil dibuat.</p>
          <p class="mt-3 break-all text-sm text-emerald-950">{{ $generatedShareLink }}</p>
          <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <button type="button" data-copy-target="dashboard-generated-link"
              class="rounded-full bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Copy Link</button>
            <button type="button" data-copy-target="dashboard-generated-share"
              class="rounded-full border border-emerald-900/20 bg-white px-4 py-2 text-sm font-semibold text-emerald-900">Copy
              Teks</button>
          </div>
          <input id="dashboard-generated-link" class="sr-only" value="{{ $generatedShareLink }}">
          <textarea id="dashboard-generated-share" class="sr-only">{{ $generatedShareText }}</textarea>
        </div>
      @endif
    </div>

    <div class="glass-card rounded-4xl p-5 sm:p-7 lg:p-8">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Ucapan terbaru</p>
      <h3 class="title-display mt-2 text-2xl text-stone-950 sm:text-3xl">Pesan tamu</h3>

      <div class="mt-6 max-h-64 space-y-3 overflow-y-auto pr-2 sm:max-h-80 sm:space-y-4">
        @forelse ($recentWishes as $wish)
          <article class="rounded-[26px] border border-stone-200 bg-white/90 p-4 sm:p-5">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="font-semibold text-stone-900">{{ $wish->guest_name }}</p>
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">{{ $wish->attendance_status }}</p>
              </div>
              <span
                class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-900">{{ $wish->created_at?->diffForHumans() }}</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-stone-600">{{ $wish->message }}</p>
          </article>
        @empty
          <p class="rounded-3xl border border-dashed border-stone-300 bg-white/70 p-6 text-sm text-stone-500">Belum ada
            ucapan masuk.</p>
        @endforelse
      </div>
    </div>
  </section>

  <section id="daftar-tamu" class="mt-6 scroll-mt-24">
    <div class="glass-card rounded-4xl p-5 sm:p-7 lg:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Daftar Tamu</p>
          <h3 class="title-display mt-2 text-2xl text-stone-950 sm:text-3xl">Link undangan yang dibuat</h3>
        </div>
        <button type="button" id="export-excel-btn" title="Export ke Excel"
          class="rounded-full border border-green-900/15 bg-green-50 px-3 py-2 text-xs font-semibold text-green-900 transition hover:bg-green-100">📊
          Export</button>
      </div>

      <form id="guest-search-form" class="mt-5" method="GET" action="{{ route('dashboard') }}">
        <label for="guest-search" class="mb-2 block text-xs font-semibold uppercase tracking-[0.28em] text-stone-500">Cari
          Tamu</label>
        <div class="flex flex-col gap-2 sm:flex-row">
          <input id="guest-search" name="q" value="{{ $searchQuery ?? '' }}"
            class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
            placeholder="Cari nama tamu atau kode undangan..." autocomplete="off">
          @if (!empty($searchQuery))
            <a href="{{ route('dashboard') }}#daftar-tamu"
              class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-center text-sm font-semibold text-stone-700">Reset</a>
          @endif
        </div>
      </form>

      <div id="guest-list-container">
        @include('dashboard.partials.guest-list', [
            'recentInvitations' => $recentInvitations,
            'searchQuery' => $searchQuery,
        ])
      </div>
    </div>
  </section>

  <div id="edit-invitation-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-950/45 px-4">
    <div class="glass-card w-full max-w-lg rounded-4xl p-5 sm:p-7">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-stone-500">Edit Tamu</p>
          <h3 class="title-display mt-2 text-2xl text-stone-950">Ubah nama undangan</h3>
        </div>
        <button type="button" id="close-edit-modal"
          class="rounded-full border border-stone-300 bg-white px-3 py-1 text-sm font-semibold text-stone-700">Tutup</button>
      </div>

      <form id="edit-invitation-form" class="mt-6 space-y-4" method="POST" data-swal-submit="Menyimpan perubahan...">
        @csrf
        @method('PUT')
        <div>
          <label for="edit-guest-name" class="mb-2 block text-sm font-semibold text-stone-700">Nama tamu</label>
          <input id="edit-guest-name" name="guest_name" required minlength="2" maxlength="120" pattern=".*\S.*"
            class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100 sm:text-base"
            placeholder="Contoh: Bapak Ahmad dan keluarga">
        </div>
        <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
          <p class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-500">Link saat ini</p>
          <p id="edit-share-link" class="mt-2 break-all text-sm font-medium text-rose-900"></p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
          <button type="button" id="copy-edit-share"
            class="rounded-full border border-emerald-900/20 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-900">Salin
            Undangan</button>
          <button type="submit" class="rounded-full bg-stone-950 px-4 py-2 text-sm font-semibold text-white">Simpan
            Perubahan</button>
        </div>
        <textarea id="edit-share-message" class="sr-only"></textarea>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js"></script>
  <script>
    const notifyAction = (icon, title) => {
      if (!window.Swal) {
        return;
      }

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 1800,
        timerProgressBar: true,
      });
    };

    const exportToExcel = async () => {
      try {
        const table = document.querySelector('.w-full.text-left.text-sm');
        if (!table) {
          notifyAction('error', 'Data tabel tidak ditemukan');
          return;
        }

        const rows = [];
        const headers = [];
        const headerCells = table.querySelectorAll('thead th');
        headerCells.forEach(cell => headers.push(cell.textContent.trim()));
        rows.push(headers);

        const bodyCells = table.querySelectorAll('tbody tr');
        bodyCells.forEach(row => {
          const cells = [];
          const tds = row.querySelectorAll('td');
          if (tds.length === 0) return;

          // Kolom 1: Nama Tamu
          const nameTd = tds[0];
          const nameEl = nameTd.querySelector('p.font-semibold');
          cells.push(nameEl ? nameEl.textContent.trim() : nameTd.textContent.trim());

          // Kolom 2: Link Personal
          const linkTd = tds[1];
          const linkEl = linkTd.querySelector('a');
          cells.push(linkEl ? linkEl.textContent.trim() : linkTd.textContent.trim());

          // Kolom 3: Dibuat
          const dateTd = tds[3];
          cells.push(dateTd ? dateTd.textContent.trim() : '');

          rows.push(cells);
        });

        if (rows.length <= 1) {
          notifyAction('warning', 'Tidak ada data untuk dieksport');
          return;
        }

        const ws = XLSX.utils.aoa_to_sheet(rows);
        ws['!cols'] = [{
          wch: 30
        }, {
          wch: 40
        }, {
          wch: 15
        }];
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Daftar Tamu');
        XLSX.writeFile(wb, `daftar-tamu-${new Date().toISOString().split('T')[0]}.xlsx`);
        notifyAction('success', 'File Excel berhasil diunduh');
      } catch (error) {
        console.error('Export error:', error);
        notifyAction('error', 'Gagal mengekspor ke Excel');
      }
    };

    document.addEventListener('click', async (event) => {
      const exportBtn = event.target.closest('#export-excel-btn');
      if (exportBtn) {
        await exportToExcel();
        return;
      }

      const copyButton = event.target.closest('[data-copy-target]');
      if (copyButton) {
        const target = document.getElementById(copyButton.dataset.copyTarget);

        if (!target) {
          return;
        }

        const value = target.value || target.textContent || '';

        try {
          await navigator.clipboard.writeText(value);
          notifyAction('success', 'Berhasil disalin');
        } catch (error) {
          notifyAction('error', 'Gagal menyalin');
        }

        return;
      }

      const editButton = event.target.closest('.js-open-edit-modal');
      if (editButton) {
        if (!editForm || !editGuestNameInput || !editShareLinkText || !editShareMessageField) {
          return;
        }

        editForm.action = editButton.dataset.updateUrl || '';
        editGuestNameInput.value = editButton.dataset.guestName || '';
        editShareLinkText.textContent = editButton.dataset.shareLink || '-';
        editShareMessageField.value = editButton.dataset.shareMessage || '';
        openModal();
        return;
      }

      const paginationLink = event.target.closest('#guest-list-container nav a');
      if (paginationLink) {
        event.preventDefault();
        await fetchGuestList(paginationLink.href, false);
      }
    });

    document.addEventListener('submit', async (event) => {
      const deleteForm = event.target.closest('.js-confirm-delete');
      if (!deleteForm) {
        return;
      }

      event.preventDefault();

      if (!window.Swal) {
        deleteForm.submit();
        return;
      }

      const result = await Swal.fire({
        icon: 'warning',
        title: 'Konfirmasi',
        text: deleteForm.dataset.confirmText || 'Lanjutkan aksi ini?',
        showCancelButton: true,
        confirmButtonText: 'Ya, lanjut',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#9f1239',
        cancelButtonColor: '#57534e',
      });

      if (result.isConfirmed) {
        Swal.fire({
          title: 'Menghapus data...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading(),
          showConfirmButton: false,
        });
        deleteForm.submit();
      }
    });

    const modal = document.getElementById('edit-invitation-modal');
    const closeModalButton = document.getElementById('close-edit-modal');
    const editForm = document.getElementById('edit-invitation-form');
    const editGuestNameInput = document.getElementById('edit-guest-name');
    const editShareLinkText = document.getElementById('edit-share-link');
    const editShareMessageField = document.getElementById('edit-share-message');
    const copyEditShareButton = document.getElementById('copy-edit-share');
    const createInvitationForm = document.querySelector('form[action="{{ route('dashboard.invitation.store') }}"]');

    const validateGuestNameInput = (input) => {
      if (!input) {
        return false;
      }

      const normalized = (input.value || '').trim();
      input.value = normalized;

      if (normalized.length < 2) {
        Swal.fire({
          icon: 'warning',
          title: 'Nama belum valid',
          text: 'Nama tamu minimal 2 karakter dan tidak boleh kosong.',
          confirmButtonColor: '#7c2d12',
        });
        input.focus();
        return false;
      }

      if (!input.checkValidity()) {
        input.reportValidity();
        return false;
      }

      return true;
    };

    const openModal = () => {
      if (!modal) {
        return;
      }

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    };

    const closeModal = () => {
      if (!modal) {
        return;
      }

      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };

    if (copyEditShareButton) {
      copyEditShareButton.addEventListener('click', async () => {
        if (!editShareMessageField) {
          return;
        }

        try {
          await navigator.clipboard.writeText(editShareMessageField.value || '');
          notifyAction('success', 'Undangan berhasil disalin');
        } catch (error) {
          notifyAction('error', 'Gagal menyalin undangan');
        }
      });
    }

    if (createInvitationForm) {
      createInvitationForm.addEventListener('submit', (event) => {
        const guestNameInput = createInvitationForm.querySelector('input[name="guest_name"]');
        if (!validateGuestNameInput(guestNameInput)) {
          event.preventDefault();
        }
      });
    }

    if (editForm) {
      editForm.addEventListener('submit', (event) => {
        if (!validateGuestNameInput(editGuestNameInput)) {
          event.preventDefault();
        }
      });
    }

    if (closeModalButton) {
      closeModalButton.addEventListener('click', closeModal);
    }

    if (modal) {
      modal.addEventListener('click', (event) => {
        if (event.target === modal) {
          closeModal();
        }
      });
    }

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeModal();
      }
    });

    const guestSearchForm = document.getElementById('guest-search-form');
    const guestSearchInput = document.getElementById('guest-search');
    const guestListContainer = document.getElementById('guest-list-container');

    const buildGuestListUrl = (url) => {
      const targetUrl = new URL(url, window.location.origin);
      targetUrl.searchParams.set('partial_guest_list', '1');
      return targetUrl;
    };

    const applyHistoryUrl = (url) => {
      const targetUrl = new URL(url, window.location.origin);
      targetUrl.searchParams.delete('partial_guest_list');
      targetUrl.hash = 'daftar-tamu';
      window.history.replaceState({}, '', targetUrl.toString());
    };

    const fetchGuestList = async (url, shouldResetPage) => {
      if (!guestListContainer) {
        return;
      }

      const fetchUrl = buildGuestListUrl(url);
      if (shouldResetPage) {
        fetchUrl.searchParams.delete('page');
      }

      try {
        const response = await fetch(fetchUrl.toString(), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) {
          throw new Error('Gagal memuat daftar tamu');
        }

        const html = await response.text();
        guestListContainer.innerHTML = html;
        applyHistoryUrl(fetchUrl.toString());
      } catch (error) {
        notifyAction('error', 'Gagal memuat daftar tamu');
      }
    };

    if (guestSearchForm && guestSearchInput) {
      let searchTimer;

      guestSearchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        fetchGuestList(`${guestSearchForm.action}?q=${encodeURIComponent((guestSearchInput.value || '').trim())}`,
          true);
      });

      guestSearchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
          fetchGuestList(
            `${guestSearchForm.action}?q=${encodeURIComponent((guestSearchInput.value || '').trim())}`, true);
        }, 350);
      });
    }

    const setMobileWidths = () => {
      document.querySelectorAll('[data-copy-target]').forEach((button) => {
        if (window.innerWidth < 640) {
          button.style.width = '100%';
        } else {
          button.style.width = '';
        }
      });
    };

    setMobileWidths();
    window.addEventListener('resize', setMobileWidths);
  </script>
@endpush
