<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ValidDocs - Editor</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #4F46E5; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h2 { margin: 0; font-size: 18px; }
        .toolbar { padding: 10px 20px; border-bottom: 1px solid #eee; display: flex; gap: 8px; align-items: center; background: #fafafa; flex-wrap: wrap; }
        .toolbar button { padding: 8px 12px; cursor: pointer; border: 1px solid #ddd; background: white; border-radius: 5px; font-size: 14px; }
        .toolbar button:hover { background: #e5e7eb; }
        .btn-save { background: #10b981 !important; color: white; border: none !important; font-weight: bold; }
        .btn-history { background: #6366f1 !important; color: white; border: none !important; font-weight: bold; }
        #editor { min-height: 500px; padding: 30px; outline: none; font-size: 16px; line-height: 1.6; }
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 20px; border-radius: 10px; width: 90%; max-width: 600px; max-height: 70vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .close { font-size: 24px; cursor: pointer; color: #666; }
        .version-list { list-style: none; padding: 0; margin: 0; }
        .version-item { padding: 12px; border: 1px solid #eee; border-radius: 6px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .version-item:hover { background: #f9fafb; }
        .version-info { flex: 1; }
        .version-note { font-weight: 600; color: #333; }
        .version-date { font-size: 12px; color: #666; }
        .version-actions { display: flex; gap: 8px; }
        .btn-preview { background: #6366f1; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-restore { background: #f59e0b; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; }
    </style>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📄 {{ $document->title }}</h2>
            <span style="font-size: 12px; opacity: 0.8;">Version History</span>
        </div>
        <div class="toolbar">
            <button id="bold"><b>B</b></button>
            <button id="italic"><i>I</i></button>
            <button id="underline"><u>U</u></button>
            <select id="fontSize">
                <option value="16px">Normal</option>
                <option value="20px">Besar</option>
                <option value="24px">Jumbo</option>
            </select>
            <button id="saveVersion" class="btn-save">💾 Simpan Versi</button>
            <button id="openHistory" class="btn-history">🕒 Riwayat</button>
        </div>
        <div id="editor"></div>
    </div>

    <div id="historyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📜 Riwayat Versi</h3>
                <span class="close" id="closeModal">&times;</span>
            </div>
            <ul id="versionList" class="version-list">
                <li style="text-align:center; color:#666;">Memuat riwayat...</li>
            </ul>
        </div>
    </div>

    <script>
        window.initialContent = @json($initialContent);
        window.documentId = {{ $document->id }};
    </script>
    @vite(['resources/js/valdidocs.js'])
</body>
</html>