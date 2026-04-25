<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Admin: list all announcements.
     */
    public function index()
    {
        $announcements = Announcement::with('author')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('announcements.index', compact('announcements'));
    }

    /**
     * Admin: show creation form.
     */
    public function create()
    {
        return view('announcements.create');
    }

    /**
     * Admin: store a new announcement.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = Auth::id();

        Announcement::create($data);

        return redirect()->route('announcements.index')
            ->with('success', 'Comunicado creado correctamente.');
    }

    /**
     * Admin: show edit form.
     */
    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    /**
     * Admin: update an announcement.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $announcement->update($data);

        return redirect()->route('announcements.index')
            ->with('success', 'Comunicado actualizado correctamente.');
    }

    /**
     * Admin: delete an announcement.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Comunicado eliminado correctamente.');
    }
}
