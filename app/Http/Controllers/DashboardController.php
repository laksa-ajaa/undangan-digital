<?php

namespace App\Http\Controllers;

use App\Models\GuestInvitation;
use App\Models\GuestWish;
use App\Models\PageVisit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function storeInvitation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
        ]);

        $shareCode = (string) Str::ulid();
        $shareMessage = $this->buildShareMessage($data['guest_name'], URL::route('invitation.show', [
            'guestName' => Str::slug($data['guest_name']),
            'guestInvitation' => $shareCode,
        ]));
        $shareLink = URL::route('invitation.show', [
            'guestName' => Str::slug($data['guest_name']),
            'guestInvitation' => $shareCode,
        ]);

        $guestInvitation = GuestInvitation::create([
            'guest_name' => $data['guest_name'],
            'share_code' => $shareCode,
            'share_message' => $shareMessage,
            'share_link' => $shareLink,
            'shared_by' => Auth::user()?->name,
        ]);

        $shareText = trim(sprintf(
            "%s\n\n%s\n\nLink undangan: %s",
            $guestInvitation->guest_name,
            $guestInvitation->share_message,
            $guestInvitation->share_link,
        ));

        return redirect()
            ->to(route('dashboard') . '#menu-client')
            ->with('dashboardGeneratedInvitation', [
                'guest_name' => $guestInvitation->guest_name,
                'share_link' => $guestInvitation->share_link,
                'share_code' => $guestInvitation->share_code,
            ])
            ->with('dashboardGeneratedShareText', $shareText)
            ->with('status', 'Tamu undangan berhasil dibuat.');
    }

    public function updateInvitation(Request $request, GuestInvitation $guestInvitation): RedirectResponse
    {
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
        ]);

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
            ->to(route('dashboard') . '#daftar-tamu')
            ->with('status', 'Tamu undangan berhasil diperbarui.');
    }

    public function destroyInvitation(GuestInvitation $guestInvitation): RedirectResponse
    {
        $guestInvitation->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Tamu undangan berhasil dihapus.');
    }

    public function index(Request $request): View
    {
        $searchQuery = trim((string) $request->query('q', ''));
        $guestInvitations = $this->buildGuestInvitationPaginator($searchQuery);

        if ($request->boolean('partial_guest_list')) {
            return view('dashboard.partials.guest-list', [
                'recentInvitations' => $guestInvitations,
                'searchQuery' => $searchQuery,
            ]);
        }

        $visitorTrend = collect(range(6, 0))->map(function (int $daysAgo): array {
            $date = Carbon::now()->subDays($daysAgo)->startOfDay();

            return [
                'label' => $date->format('d M'),
                'count' => PageVisit::whereDate('created_at', $date)->count(),
            ];
        });

        return view('dashboard.index', [
            'totalInvitations' => GuestInvitation::count(),
            'totalVisits' => PageVisit::count(),
            'totalWishes' => GuestWish::count(),
            'todayVisits' => PageVisit::whereDate('created_at', today())->count(),
            'recentInvitations' => $guestInvitations,
            'recentWishes' => GuestWish::latest()->limit(8)->get(),
            'visitorTrend' => $visitorTrend,
            'peakVisitorCount' => max($visitorTrend->pluck('count')->all() ?: [0]),
            'searchQuery' => $searchQuery,
        ]);
    }

    private function buildGuestInvitationPaginator(string $searchQuery): LengthAwarePaginator
    {
        return $this->buildGuestInvitationQuery($searchQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->fragment('daftar-tamu');
    }

    private function buildGuestInvitationQuery(string $searchQuery): Builder
    {
        return GuestInvitation::query()
            ->when($searchQuery !== '', function (Builder $query) use ($searchQuery) {
                $query->where(function (Builder $innerQuery) use ($searchQuery) {
                    $innerQuery
                        ->where('guest_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('share_code', 'like', '%' . $searchQuery . '%');
                });
            });
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
}
