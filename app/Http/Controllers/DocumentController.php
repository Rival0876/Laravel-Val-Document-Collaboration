<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    // 1. Halaman Editor
    public function edit(Document $document)
    {
        $latestVersion = $document->versions()->latest()->first();
        $initialContent = $latestVersion ? $latestVersion->content_html : '';
        
        return view('editor', compact('document', 'initialContent'));
    }

    // 2. Simpan Versi Baru
    public function saveVersion(Request $request, Document $document)
    {
        $request->validate([
            'content' => 'required',
            'note' => 'nullable|string'
        ]);

        DocumentVersion::create([
            'document_id' => $document->id,
            'user_id' => 1,
            'content_html' => $request->content,
            'note' => $request->note ?? 'Versi Tersimpan'
        ]);

        return response()->json(['message' => 'Versi berhasil disimpan!']);
    }

    // 3. Ambil Daftar Versi
    public function getVersions(Document $document)
    {
        $versions = $document->versions()
            ->latest()
            ->get(['id', 'note', 'created_at', 'content_html']);
            
        return response()->json($versions);
    }

    // 4. ✅ Ambil Satu Versi (untuk Preview) - INI YANG BARU!
    public function getVersion(Document $document, DocumentVersion $version)
    {
        // Validasi bahwa versi ini milik dokumen yang benar
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

    // 5. Restore Versi
    public function restoreVersion(Document $document, DocumentVersion $version)
    {
        // Validasi bahwa versi ini milik dokumen yang benar
        if ($version->document_id !== $document->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message' => 'Dokumen berhasil dikembalikan!',
            'content' => $version->content_html
        ]);
    }

    // 6. Buat Dokumen Baru
    public function create()
    {
        $document = Document::create([
            'title' => 'Dokumen Baru - ' . now()->format('d/m/Y H:i'),
            'user_id' => 1,
        ]);
        return redirect()->route('documents.edit', $document->id);
    }

    // 7. Dashboard
    public function index()
    {
        $documents = Document::where('user_id', 1)->latest()->get();
        return view('dashboard', compact('documents'));
    }
    
    // 8. Rename Dokumen
    public function rename(Request $request, Document $document)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $document->update(['title' => $request->title]);
        return response()->json(['success' => true]);
    }
}