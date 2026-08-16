<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LivePengajian;
use Illuminate\Http\Request;

class LivePengajianController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', LivePengajian::class);

        $query = LivePengajian::orderByDesc('jadwal_mulai');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        $items = $query->paginate(15)->withQueryString();

        $countLive      = LivePengajian::where('status', 'live')->count();
        $countScheduled = LivePengajian::where('status', 'scheduled')->count();
        $countEnded     = LivePengajian::where('status', 'ended')->count();

        return view('dashboard.live-pengajian.index', compact(
            'items', 'countLive', 'countScheduled', 'countEnded'
        ));
    }

    public function create()
    {
        $this->authorize('create', LivePengajian::class);
        return view('dashboard.live-pengajian.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', LivePengajian::class);

        $data = $request->validate([
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'nullable|string',
            'platform'       => 'required|in:zoom,gmeet,youtube',
            'link_url'       => 'required|url|max:500',
            'meeting_id'     => 'nullable|string|max:100',
            'passcode'       => 'nullable|string|max:100',
            'jadwal_mulai'   => 'required|date',
            'jadwal_selesai' => 'nullable|date|after:jadwal_mulai',
            'status'         => 'nullable|in:scheduled,live,ended',
            'thumbnail_url'  => 'nullable|url|max:500',
        ]);

        LivePengajian::create(array_merge($data, [
            'status'     => $data['status'] ?? 'scheduled',
            'created_by' => auth()->id(),
        ]));

        return redirect(tenant_route('dashboard.live-pengajian.index'))
            ->with('success', 'Jadwal live pengajian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = LivePengajian::findOrFail((int) $id);
        $this->authorize('update', $item);
        return view('dashboard.live-pengajian.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = LivePengajian::findOrFail((int) $id);
        $this->authorize('update', $item);

        $data = $request->validate([
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'nullable|string',
            'platform'       => 'required|in:zoom,gmeet,youtube',
            'link_url'       => 'required|url|max:500',
            'meeting_id'     => 'nullable|string|max:100',
            'passcode'       => 'nullable|string|max:100',
            'jadwal_mulai'   => 'required|date',
            'jadwal_selesai' => 'nullable|date|after:jadwal_mulai',
            'status'         => 'required|in:scheduled,live,ended',
            'thumbnail_url'  => 'nullable|url|max:500',
        ]);

        $item->update($data);

        return redirect(tenant_route('dashboard.live-pengajian.index'))
            ->with('success', 'Jadwal live pengajian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = LivePengajian::findOrFail((int) $id);
        $this->authorize('delete', $item);
        $item->delete();

        return redirect(tenant_route('dashboard.live-pengajian.index'))
            ->with('success', 'Jadwal live pengajian berhasil dihapus.');
    }

    public function setStatus(Request $request, $id)
    {
        $item = LivePengajian::findOrFail((int) $id);
        $this->authorize('setStatus', $item);

        $request->validate(['status' => 'required|in:scheduled,live,ended']);
        $item->update(['status' => $request->status]);

        return back()->with('success', 'Status live pengajian diperbarui.');
    }
}
