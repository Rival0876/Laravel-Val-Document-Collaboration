<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    // 1. Halaman Editor
    public function edit(Document $document)
    {
        $latestVersion = $document->versions()->latest()->first();
        $initialContent = $latestVersion ? $latestVersion->content_html : '';
        return view('editor', compact('document', 'initialContent'));
    }

    // 2. Simpan Versi Baru (Track User Asli)
    public function saveVersion(Request $request, Document $document)
    {
        $request->validate([
            'content' => 'required',
            'note' => 'nullable|string'
        ]);

        $version = DocumentVersion::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(), // ✅ Track user login
            'content_html' => $request->content,
            'note' => $request->note ?? 'Versi ' . (DocumentVersion::where('document_id', $document->id)->count() + 1)
        ]);

        return response()->json(['message' => 'Versi berhasil disimpan!', 'version_id' => $version->id]);
    }

    // 3. Ambil Daftar Versi + Nama User
    public function getVersions(Document $document)
    {
        $versions = $document->versions()
            ->with('user:id,name') // ✅ Load nama user
            ->latest()
            ->get(['id', 'note', 'created_at', 'content_html', 'user_id']);
            
        return response()->json($versions);
    }

    // 4. Ambil Satu Versi (untuk Preview)
    public function getVersion(Document $document, DocumentVersion $version)
    {
        if ($version->document_id !== $document->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $version->id,
            'note' => $version->note,
            'created_at' => $version->created_at,
            'content_html' => $version->content_html
        ]);
    }

    // 5. Diff 2 Versi (Who Edited What)
    public function compareVersions(Request $request, Document $document)
    {
        $request->validate([
            'v1' => 'required|exists:document_versions,id',
            'v2' => 'required|exists:document_versions,id'
        ]);
        
        $ver1 = DocumentVersion::findOrFail($request->v1);
        $ver2 = DocumentVersion::findOrFail($request->v2);

        if ($ver1->document_id !== $document->id || $ver2->document_id !== $document->id) {
            return response()->json(['error' => 'Invalid document'], 403);
        }

        // Simple text diff (strip HTML tags for clean comparison)
        $text1 = strip_tags($ver1->content_html);
        $text2 = strip_tags($ver2->content_html);

        $diff = [];
        $lines1 = explode("\n", wordwrap($text1, 50));
        $lines2 = explode("\n", wordwrap($text2, 50));
        $max = max(count($lines1), count($lines2));

        for ($i = 0; $i < $max; $i++) {
            $l1 = $lines1[$i] ?? '';
            $l2 = $lines2[$i] ?? '';
            if ($l1 !== $l2) {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $l1,
                    'new' => $l2
                ];
            }
        }

        return response()->json([
            'diff' => $diff,
            'v1_user' => $ver1->user?->name ?? 'Unknown',
            'v2_user' => $ver2->user?->name ?? 'Unknown',
            'v1_time' => $ver1->created_at,
            'v2_time' => $ver2->created_at
        ]);
    }

    // 6. Restore Versi
    public function restoreVersion(Document $document, DocumentVersion $version)
    {
        if ($version->document_id !== $document->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message' => 'Dokumen berhasil dikembalikan!',
            'content' => $version->content_html
        ]);
    }

    // 7. Buat Dokumen Baru
    public function create()
    {
        $document = Document::create([
            'title' => 'Dokumen Baru - ' . now()->format('d/m/Y H:i'),
            'user_id' => Auth::id(),
        ]);
        return redirect()->route('documents.edit', $document->id);
    }

    // 8. Dashboard
    public function index()
    {
        $documents = Document::where('user_id', Auth::id())->latest()->get();
        return view('dashboard', compact('documents'));
    }
    
    // 9. Rename Dokumen
    public function rename(Request $request, Document $document)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $document->update(['title' => $request->title]);
        return response()->json(['success' => true]);
    }

    
}