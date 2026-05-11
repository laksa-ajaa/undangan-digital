<?php

namespace App\Http\Controllers;

use App\Models\GuestInvitation;
use App\Models\GuestWish;
use App\Models\PageVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse as HttpRedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class WeddingInvitationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->recordVisit(null, 'landing', $request);

        return response($this->buildInvitationHtml(null, GuestWish::latest()->limit(6)->get()));
    }

    public function show(Request $request, string $guestName, GuestInvitation $guestInvitation): Response
    {
        $this->recordVisit($guestInvitation, 'invitation', $request);

        return response($this->buildInvitationHtml($guestInvitation, GuestWish::latest()->limit(6)->get()));
    }

    public function listWishes(Request $request): JsonResponse
    {
        $wishPaginator = GuestWish::latest()->paginate(6);

        return response()->json([
            'items' => $wishPaginator->getCollection()->map(function (GuestWish $wish): array {
                return [
                    'name' => $wish->guest_name,
                    'message' => $wish->message,
                    'date' => optional($wish->created_at)->locale('id')->translatedFormat('d F Y'),
                ];
            })->values(),
            'current_page' => $wishPaginator->currentPage(),
            'last_page' => $wishPaginator->lastPage(),
            'next_page_url' => $wishPaginator->nextPageUrl(),
            'total' => $wishPaginator->total(),
        ]);
    }

    public function storeInvitation(Request $request): RedirectResponse
    {
        $data = $this->validateInvitationData($request);

        $shareCode = (string) Str::ulid();
        $shareLink = URL::route('invitation.show', [
            'guestName' => Str::slug($data['guest_name']),
            'guestInvitation' => $shareCode,
        ]);

        $guestInvitation = GuestInvitation::create([
            'guest_name' => $data['guest_name'],
            'share_code' => $shareCode,
            'share_message' => $this->buildShareMessage($data['guest_name'], $shareLink),
            'share_link' => $shareLink,
            'shared_by' => Auth::user()?->name,
        ]);

        return redirect()
            ->route('invitation.index')
            ->with('generatedInvitation', [
                'guest_name' => $guestInvitation->guest_name,
                'share_link' => $guestInvitation->share_link,
                'share_code' => $guestInvitation->share_code,
            ])
            ->with('generatedShareText', $guestInvitation->share_message);
    }

    public function updateInvitation(Request $request, GuestInvitation $guestInvitation): RedirectResponse
    {
        $data = $this->validateInvitationData($request);
        $shareLink = URL::route('invitation.show', [
            'guestName' => Str::slug($data['guest_name']),
            'guestInvitation' => $guestInvitation->share_code,
        ]);

        $guestInvitation->update([
            'guest_name' => $data['guest_name'],
            'share_link' => $shareLink,
            'share_message' => $this->buildShareMessage($data['guest_name'], $shareLink),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('dashboardGeneratedInvitation', [
                'guest_name' => $guestInvitation->guest_name,
                'share_link' => $guestInvitation->share_link,
                'share_code' => $guestInvitation->share_code,
            ])
            ->with('dashboardGeneratedShareText', $guestInvitation->share_message)
            ->with('dashboardEditMode', $guestInvitation->share_code);
    }

    public function destroyInvitation(GuestInvitation $guestInvitation): RedirectResponse
    {
        $guestInvitation->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Tamu undangan berhasil dihapus.');
    }

    public function showLegacy(GuestInvitation $guestInvitation): HttpRedirectResponse
    {
        return redirect()->route('invitation.show', [
            'guestName' => Str::slug($guestInvitation->guest_name),
            'guestInvitation' => $guestInvitation->share_code,
        ]);
    }

    private function validateInvitationData(Request $request): array
    {
        return $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
            'share_message' => ['nullable', 'string', 'max:500'],
        ]);
    }

    public function storeWish(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'guest_invitation_code' => ['nullable', 'string'],
            'guest_name' => ['required', 'string', 'max:120'],
            'attendance_status' => ['required', 'in:hadir,tidak_hadir'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $guestInvitation = null;

        if (! empty($data['guest_invitation_code'])) {
            $guestInvitation = GuestInvitation::where('share_code', $data['guest_invitation_code'])->first();
        }

        $guestWish = GuestWish::create([
            'guest_invitation_id' => $guestInvitation?->id,
            'guest_name' => $data['guest_name'],
            'attendance_status' => $data['attendance_status'],
            'message' => $data['message'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Ucapan Anda sudah tersimpan.',
                'wish' => [
                    'name' => $guestWish->guest_name,
                    'message' => $guestWish->message,
                    'date' => optional($guestWish->created_at)->locale('id')->translatedFormat('d F Y'),
                ],
            ]);
        }

        return back()->with('wishStatus', 'Ucapan Anda sudah tersimpan.');
    }

    private function recordVisit(?GuestInvitation $guestInvitation, string $source, Request $request): void
    {
        PageVisit::create([
            'guest_invitation_id' => $guestInvitation?->id,
            'source' => $source,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }

    private function buildShareMessage(string $guestName, string $shareLink): string
    {
        return trim(<<<TEXT
Assalamu’alaikum Wr. Wb.

Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud mengundang Bapak/Ibu/Saudara/i untuk hadir serta memberikan doa restu pada acara pernikahan kami.

Kepada Yth.
{$guestName}

Merupakan suatu kebahagiaan dan kehormatan bagi kami apabila Bapak/Ibu/Saudara/i berkenan meluangkan waktu untuk hadir di hari istimewa kami:

✨ Wafii & Tasya ✨

Hari bahagia ini menjadi awal perjalanan baru bagi kami dalam membangun ikatan suci pernikahan. Oleh karena itu, kehadiran serta doa restu dari Bapak/Ibu/Saudara/i akan menjadi hadiah terindah dan bermakna bagi kami.

Untuk detail acara lengkap, silakan membuka undangan digital melalui tautan berikut:

🔗 {$shareLink}

Kami berharap Bapak/Ibu/Saudara/i dapat turut menjadi bagian dari momen berharga ini.

Atas perhatian, doa, dan kehadirannya kami ucapkan terima kasih.

Wassalamu’alaikum Wr. Wb.
TEXT);
    }

    private function buildInvitationHtml(?GuestInvitation $guestInvitation, Collection $recentWishes): string
    {
        $template = file_get_contents(base_path('wafii_tasya_wedding.html'));

        if ($template === false) {
            abort(500, 'Template undangan tidak dapat dibaca.');
        }

        $wishes = $recentWishes->map(function (GuestWish $wish): array {
            return [
                'name' => $wish->guest_name,
                'message' => $wish->message,
                'date' => optional($wish->created_at)->locale('id')->translatedFormat('d F Y'),
            ];
        })->values();

        $payload = [
            'guestName' => $guestInvitation?->guest_name ?? 'Bapak/Ibu/Saudara/i',
            'guestInvitationCode' => $guestInvitation?->share_code,
            'wishes' => $wishes,
            'wishesTotal' => GuestWish::count(),
            'wishesListUrl' => route('wishes.list'),
            'wishPostUrl' => route('wishes.store'),
            'csrfToken' => csrf_token(),
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $responsiveStyles = <<<'HTML'
<style id="mobile-responsive-overrides">
        body {
            overflow-x: hidden;
        }

        #cover,
        #hero,
        #quran,
        #bride-groom,
        #save-date,
        #akad,
        #gallery,
        #rundown,
        #gift,
        #ucapan,
        #penutup {
            width: 100%;
        }

        .cover-inner,
        #hero .hero-content,
        .quran-text,
        .bg-header,
        .invite-text,
        .person-card,
        .save-content,
        .akad-content,
        .gift-desc,
        .ucapan-header,
        .penutup-text {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .cover-title {
            font-size: clamp(2.4rem, 15vw, 4rem) !important;
            line-height: 1.02 !important;
        }

        .cover-guest {
            font-size: clamp(1.2rem, 5vw, 1.7rem) !important;
            word-break: break-word;
        }

        .btn-buka,
        .btn-maps,
        .btn-kirim,
        .btn-copy {
            width: 100%;
            justify-content: center;
        }

        .hero-name,
        .gallery-title,
        .rundown-title,
        .gift-title,
        .ucapan-title,
        .save-label,
        .penutup-text {
            word-break: break-word;
            text-wrap: balance;
        }

        .person-photo {
            width: min(72vw, 220px);
            height: auto;
            aspect-ratio: 220 / 300;
        }

        .save-photo,
        .save-overlay {
            height: 40vh;
        }

        .countdown {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .countdown-box {
            max-width: none !important;
            min-width: 0 !important;
        }

        .gallery-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 18px !important;
            padding: 0 22px 18px !important;
            max-width: 560px !important;
            margin: 0 auto !important;
        }

        .gallery-grid .full {
            grid-column: 1 / -1 !important;
            justify-self: center !important;
            max-width: 420px !important;
        }

        .gallery-grid img,
        .gallery-grid .full img {
            height: 100% !important;
            aspect-ratio: auto !important;
        }

        .rundown-item,
        .gift-desc,
        .akad-address,
        .quran-ayat,
        .person-parents,
        .penutup-msg,
        .ucapan-item-msg {
            font-size: 1rem !important;
            line-height: 1.65 !important;
        }

        .form-input,
        .form-select,
        .form-textarea {
            font-size: 1rem !important;
        }

        .bank-card,
        .form-card,
        .ucapan-item {
            border-radius: 14px !important;
        }

        .ucapan-item-header {
            gap: 10px;
        }

        .ucapan-list-shell {
            max-height: 420px;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 14px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(214, 199, 185, 0.55);
            border-radius: 24px;
            box-shadow: 0 12px 30px rgba(74, 35, 22, 0.08);
        }

        .ucapan-list-shell::-webkit-scrollbar {
            width: 8px;
        }

        .ucapan-list-shell::-webkit-scrollbar-thumb {
            background: rgba(124, 58, 34, 0.25);
            border-radius: 999px;
        }

        .person-connector {
            padding: 4px 0 !important;
        }
    /* ==============================================
       Desktop Portrait Mode — TikTok / Shorts style
       ============================================== */
    .bg-blur-sides {
        display: none;
    }

    @media (min-width: 768px) {
        .bg-blur-sides {
            display: block;
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(196, 136, 72, 0.45), transparent 40%),
                radial-gradient(circle at 85% 15%, rgba(109, 36, 36, 0.30), transparent 38%),
                radial-gradient(circle at 50% 85%, rgba(220, 150, 60, 0.25), transparent 40%),
                linear-gradient(160deg, #f7f1e7 0%, #edd9b5 45%, #f3e4cc 100%);
            filter: blur(28px) saturate(1.6) brightness(0.78);
            transform: scale(1.08);
        }

        .bg-blur-sides::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(10, 4, 1, 0.48);
        }

        #portrait-frame {
            position: relative;
            z-index: 1;
            max-width: 480px;
            margin: 0 auto;
            box-shadow:
                0 0 0 1px rgba(255, 235, 200, 0.18),
                0 8px 32px rgba(0, 0, 0, 0.18),
                0 32px 120px rgba(0, 0, 0, 0.45);
        }

        /* Constrain the fixed opening cover to portrait width on desktop */
        #cover {
            left: 50% !important;
            right: auto !important;
            width: 480px !important;
            transform: translateX(-50%) !important;
        }
    }

    /* ==============================================
       Gallery Premium Frame Styles
       ============================================== */
    .gallery-grid div {
        background: #fdfbf7 !important;
        padding: 12px !important;
        border-radius: 6px !important;
        box-shadow: 
            0 10px 25px rgba(0, 0, 0, 0.4),
            inset 0 0 0 1px rgba(196, 136, 72, 0.3),
            inset 0 0 0 5px #fdfbf7,
            inset 0 0 0 6px rgba(196, 136, 72, 0.8) !important;
        border: none !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        display: flex !important;
        flex-direction: column !important;
        position: relative !important;
        min-width: 0 !important;
        aspect-ratio: 3 / 4 !important;
    }
    
    .gallery-grid .full {
        aspect-ratio: 3 / 4 !important;
    }

    .gallery-grid div:hover {
        transform: translateY(-4px) !important;
        box-shadow: 
            0 15px 35px rgba(0, 0, 0, 0.5),
            inset 0 0 0 1px rgba(196, 136, 72, 0.5),
            inset 0 0 0 5px #fdfbf7,
            inset 0 0 0 6px rgba(196, 136, 72, 1) !important;
        z-index: 10 !important;
        background: #fdfbf7 !important;
    }

    .gallery-grid img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: center top !important;
        border-radius: 3px !important;
        display: block !important;
    }

    .gallery-grid .full img {
        height: 100% !important;
        object-position: center top !important;
    }
    
    @media (max-width: 768px) {
        .gallery-grid img {
            height: 100% !important;
        }
        .gallery-grid .full img {
            height: 100% !important;
        }
    }

    /* ==============================================
       Save The Date Refinements
       ============================================== */
    #save-date {
        position: relative !important;
        min-height: 0 !important;
        display: flex !important;
    }

    #save-date .save-photo {
        height: auto !important;
        max-height: none !important;
    }

    #save-date .save-overlay {
        display: none !important;
    }

    #save-date .save-content {
        position: absolute !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        background: transparent !important;
        padding: 0 24px 40px !important;
        z-index: 10 !important;
    }

    #save-date .save-label {
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5) !important;
    }
    
    #save-date .countdown {
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: center !important;
        gap: 8px !important;
    }
    
    #save-date .countdown-box {
        min-width: 0 !important;
        flex: 1 1 0 !important;
        padding: 12px 6px 8px !important;
        max-width: none !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
    }
    
    #save-date .countdown-num {
        font-size: clamp(1.2rem, 5vw, 2rem) !important;
    }
    
    #save-date .countdown-label {
        font-size: clamp(0.6rem, 2.5vw, 0.75rem) !important;
    }
</style>
HTML;

        $injectedScript = <<<'HTML'
<script>
(function () {
              const data = __PAYLOAD__;

    const coverGuest = document.querySelector('.cover-guest');
    if (coverGuest) {
        coverGuest.textContent = data.guestName;
    }

    const ucapanList = document.getElementById('ucapan-list');
    const ucapanCount = document.getElementById('ucapan-count');
    const wishesListUrl = data.wishesListUrl;
    let wishTotal = Number(data.wishesTotal || 0);
    let nextWishesPageUrl = wishesListUrl && wishTotal > (data.wishes || []).length ? `${wishesListUrl}?page=2` : null;
    let wishesLoading = false;

    const loadMoreButton = document.createElement('button');
    loadMoreButton.type = 'button';
    loadMoreButton.className = 'btn-kirim mt-4';
    loadMoreButton.textContent = 'MUAT UCAPAN LAINNYA';
    loadMoreButton.style.display = 'none';

    const renderWish = (wish) => {
        if (!ucapanList) return;

        const item = document.createElement('div');
        item.className = 'ucapan-item';
        item.innerHTML = `
            <div class="ucapan-item-header">
                <p class="ucapan-item-name">${wish.name}</p>
            </div>
            <p class="ucapan-item-msg">${wish.message}</p>
            <p class="ucapan-item-date">${wish.date || ''}</p>
        `;

        ucapanList.appendChild(item);
    };

    const updateWishCounter = () => {
        if (ucapanCount) {
            ucapanCount.textContent = `Daftar Ucapan Tamu (${wishTotal})`;
        }
    };

    const ensureLoadMoreButton = () => {
        if (!ucapanList || loadMoreButton.parentElement) {
            return;
        }

        ucapanList.insertAdjacentElement('afterend', loadMoreButton);
    };

    const ensureScrollableShell = () => {
        if (!ucapanList || document.getElementById('ucapan-list-shell')) {
            return;
        }

        const shell = document.createElement('div');
        shell.id = 'ucapan-list-shell';
        shell.className = 'ucapan-list-shell';
        ucapanList.parentNode.insertBefore(shell, ucapanList);
        shell.appendChild(ucapanList);
    };

    const setLoadMoreVisibility = (visible) => {
        loadMoreButton.style.display = visible ? 'block' : 'none';
    };

    const loadMoreWishes = async () => {
        if (!nextWishesPageUrl || wishesLoading) {
            return;
        }

        wishesLoading = true;
        loadMoreButton.disabled = true;
        loadMoreButton.textContent = 'MEMUAT...';

        try {
            const response = await fetch(nextWishesPageUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Gagal memuat ucapan lainnya');
            }

            const result = await response.json();

            (result.items || []).forEach(renderWish);
            nextWishesPageUrl = result.next_page_url || null;
            setLoadMoreVisibility(Boolean(nextWishesPageUrl));
            updateWishCounter();
        } catch (error) {
            console.error(error);
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memuat ucapan',
                    text: 'Silakan coba lagi nanti.',
                    confirmButtonColor: '#7c2d12',
                });
            }
        } finally {
            wishesLoading = false;
            loadMoreButton.disabled = false;
            loadMoreButton.textContent = 'MUAT UCAPAN LAINNYA';
        }
    };

    if (ucapanList) {
        ensureScrollableShell();
        ucapanList.innerHTML = '';
        (data.wishes || []).forEach(renderWish);
        ensureLoadMoreButton();
        setLoadMoreVisibility(Boolean(nextWishesPageUrl));
    }

    updateWishCounter();

    loadMoreButton.addEventListener('click', loadMoreWishes);

    window.kirimUcapan = async function kirimUcapan() {
        const namaEl = document.getElementById('inp-nama');
        const hadirEl = document.getElementById('inp-hadir');
        const ucapanEl = document.getElementById('inp-ucapan');
        const submitButton = document.querySelector('.btn-kirim');

        if (!namaEl || !hadirEl || !ucapanEl) return;

        if (submitButton?.dataset?.sending === '1') {
            return;
        }

        const nama = namaEl.value.trim();
        const hadir = hadirEl.value;
        const ucapan = ucapanEl.value.trim();

        if (!nama || !hadir || !ucapan) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap',
                    text: 'Mohon isi nama, konfirmasi kehadiran, dan ucapan terlebih dahulu.',
                    confirmButtonColor: '#7c2d12'
                });
            }
            return;
        }

            if (!['hadir', 'tidak_hadir'].includes(hadir)) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi kehadiran tidak valid',
                        text: 'Silakan pilih Hadir atau Tidak Hadir.',
                        confirmButtonColor: '#7c2d12'
                    });
                }
                return;
            }

        if (submitButton) {
            submitButton.dataset.sending = '1';
            submitButton.disabled = true;
            submitButton.textContent = 'MENGIRIM...';
        }

        const payload = {
            guest_invitation_code: data.guestInvitationCode || '',
            guest_name: nama,
            attendance_status: hadir,
            message: ucapan,
        };

        try {
            if (window.Swal) {
                Swal.fire({
                    title: 'Mengirim ucapan...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    showConfirmButton: false,
                });
            }

            const response = await fetch(data.wishPostUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': data.csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json().catch(() => null);

            if (!response.ok || !result) {
                throw new Error(result?.message || 'Gagal menyimpan ucapan');
            }

            renderWish(result.wish || {
                name: nama,
                message: ucapan,
                date: '',
            });
            wishTotal += 1;

            namaEl.value = '';
            hadirEl.value = 'hadir';
            ucapanEl.value = '';

            updateWishCounter();

            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: result.message || 'Ucapan berhasil dikirim',
                    showConfirmButton: false,
                    timer: 1800,
                    timerProgressBar: true,
                });
            }
        } catch (error) {
            console.error(error);
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal mengirim',
                    text: error?.message || 'Ucapan belum berhasil dikirim. Silakan coba lagi.',
                    confirmButtonColor: '#7c2d12',
                });
            }
        } finally {
            if (submitButton) {
                submitButton.dataset.sending = '0';
                submitButton.disabled = false;
                submitButton.textContent = 'KIRIM UCAPAN';
            }
        }
    };
})();
</script>
HTML;

        $injectedScript = str_replace('__PAYLOAD__', $jsonPayload ?: '{}', $injectedScript);

        $sweetAlertScript = '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

        $template = str_replace('</head>', $sweetAlertScript . $responsiveStyles . '</head>', $template);

        // Wrap body content in portrait frame for desktop TikTok-style layout
        $template = preg_replace_callback(
            '/<body([^>]*)>/i',
            fn ($m) => '<body' . $m[1] . '>' .
                '<div class="bg-blur-sides" aria-hidden="true"></div>' .
                '<div id="portrait-frame">',
            $template
        );

        $template = str_replace('</body>', '</div>' . $injectedScript . '</body>', $template);

        return $template;
    }
}
