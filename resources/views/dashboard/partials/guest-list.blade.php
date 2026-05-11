<div
  class="mt-6 overflow-hidden rounded-[30px] border border-stone-200 bg-white/90 shadow-[0_10px_30px_rgba(74,35,22,0.08)]">
  <div class="border-b border-stone-200 bg-linear-to-r from-stone-950 to-rose-900 px-5 py-4 text-white">
    <div class="flex items-center justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.32em] text-white/60">Guest Registry</p>
        <h4 class="mt-1 text-lg font-semibold">Daftar tamu yang sudah dibuat</h4>
      </div>
      <span
        class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">{{ $recentInvitations->total() }}
        data</span>
    </div>
  </div>

  <div class="p-4 md:hidden">
    <div class="space-y-4">
      @forelse ($recentInvitations as $invitation)
        <article class="w-full max-w-full box-border rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              <div
                class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stone-950 text-xs font-semibold text-white">
                {{ strtoupper(substr($invitation->guest_name, 0, 2)) }}
              </div>
              <div>
                <p class="font-semibold text-stone-950">{{ $invitation->guest_name }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ $invitation->created_at?->format('d M Y, H:i') }}</p>
              </div>
            </div>
            <span
              class="rounded-full bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-900">{{ $invitation->shared_by ?: 'Dashboard' }}</span>
          </div>

          <div class="mt-4 w-full max-w-full overflow-hidden rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
            <a href="{{ $invitation->share_link }}"
              class="block break-all wrap-break-word text-sm font-medium text-rose-900 underline decoration-rose-300 underline-offset-4">
              {{ \Illuminate\Support\Str::limit($invitation->share_link, 60) }}
            </a>
            <p class="mt-2 text-xs text-stone-500">Kode: {{ $invitation->share_code }}</p>
          </div>

          <div class="mt-4 grid grid-cols-2 gap-2">
            <button type="button" data-copy-target="link-{{ $invitation->id }}"
              class="rounded-full border border-emerald-900/15 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-900">Salin
              Link</button>
            <button type="button" data-copy-target="share-{{ $invitation->id }}"
              class="rounded-full border border-amber-900/15 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900">Salin
              Undangan</button>
            <button type="button"
              class="js-open-edit-modal rounded-full border border-stone-300 bg-white px-3 py-2 text-center text-xs font-semibold text-stone-700"
              data-update-url="{{ route('dashboard.invitation.update', $invitation) }}"
              data-guest-name="{{ $invitation->guest_name }}" data-share-link="{{ $invitation->share_link }}"
              data-share-message="{{ $invitation->share_message }}">Edit</button>
            <form method="POST" action="{{ route('dashboard.invitation.destroy', $invitation) }}"
              class="js-confirm-delete" data-confirm-text="Hapus tamu ini?">
              @csrf
              @method('DELETE')
              <button
                class="w-full rounded-full border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-900">Hapus</button>
            </form>
          </div>

          <input id="link-{{ $invitation->id }}" class="sr-only" value="{{ $invitation->share_link }}">
          <textarea id="share-{{ $invitation->id }}" class="sr-only">{{ $invitation->share_message }}</textarea>
        </article>
      @empty
        <p class="rounded-3xl border border-dashed border-stone-300 bg-white/70 p-6 text-center text-sm text-stone-500">
          {{ !empty($searchQuery) ? 'Tamu tidak ditemukan untuk kata kunci tersebut.' : 'Belum ada tamu yang dibuat.' }}
        </p>
      @endforelse
    </div>
  </div>

  <div class="hidden overflow-x-auto md:block">
    <table class="min-w-230 w-full text-left text-sm">
      <thead class="bg-stone-50 text-stone-500">
        <tr>
          <th class="px-5 py-4 text-xs font-semibold uppercase tracking-[0.18em]">Nama Tamu</th>
          <th class="px-5 py-4 text-xs font-semibold uppercase tracking-[0.18em]">Link Personal</th>
          <th class="px-5 py-4 text-xs font-semibold uppercase tracking-[0.18em]">Aksi</th>
          <th class="px-5 py-4 text-xs font-semibold uppercase tracking-[0.18em]">Dibuat</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-stone-100 bg-white">
        @forelse ($recentInvitations as $invitation)
          <tr class="transition hover:bg-rose-50/40">
            <td class="px-5 py-4 align-top">
              <div class="flex items-start gap-3">
                <div
                  class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stone-950 text-xs font-semibold text-white">
                  {{ strtoupper(substr($invitation->guest_name, 0, 2)) }}
                </div>
                <div>
                  <p class="font-semibold text-stone-950">{{ $invitation->guest_name }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ $invitation->shared_by ?: 'Dashboard' }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 align-top">
              <div class="max-w-90 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <a href="{{ $invitation->share_link }}"
                  class="block wrap-break-word text-sm font-medium text-rose-900 underline decoration-rose-300 underline-offset-4">
                  {{ \Illuminate\Support\Str::limit($invitation->share_link, 52) }}
                </a>
                <p class="mt-2 text-xs text-stone-500">Kode: {{ $invitation->share_code }}</p>
              </div>
            </td>
            <td class="px-5 py-4 align-top">
              <div class="flex flex-wrap gap-2">
                <button type="button" data-copy-target="link-{{ $invitation->id }}"
                  class="rounded-full border border-emerald-900/15 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-900 transition hover:bg-emerald-100">Salin
                  Link</button>
                <button type="button" data-copy-target="share-{{ $invitation->id }}"
                  class="rounded-full border border-amber-900/15 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900 transition hover:bg-amber-100">Salin
                  Undangan</button>
                <button type="button"
                  class="js-open-edit-modal rounded-full border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-400 hover:bg-stone-50"
                  data-update-url="{{ route('dashboard.invitation.update', $invitation) }}"
                  data-guest-name="{{ $invitation->guest_name }}" data-share-link="{{ $invitation->share_link }}"
                  data-share-message="{{ $invitation->share_message }}">Edit</button>
                <form method="POST" action="{{ route('dashboard.invitation.destroy', $invitation) }}"
                  class="js-confirm-delete" data-confirm-text="Hapus tamu ini?">
                  @csrf
                  @method('DELETE')
                  <button
                    class="rounded-full border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-900 transition hover:bg-rose-100">Hapus</button>
                </form>
                <input id="link-{{ $invitation->id }}" class="sr-only" value="{{ $invitation->share_link }}">
                <textarea id="share-{{ $invitation->id }}" class="sr-only">{{ $invitation->share_message }}</textarea>
              </div>
            </td>
            <td class="px-5 py-4 align-top text-stone-500">{{ $invitation->created_at?->format('d M Y, H:i') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="px-5 py-10 text-center text-stone-500">
              {{ !empty($searchQuery) ? 'Tamu tidak ditemukan untuk kata kunci tersebut.' : 'Belum ada tamu yang dibuat.' }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if ($recentInvitations->hasPages())
    <div class="border-t border-stone-200 bg-white px-4 py-4 sm:px-5">
      {{ $recentInvitations->links() }}
    </div>
  @endif
</div>
