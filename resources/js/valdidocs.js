import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Bold from '@tiptap/extension-bold'
import Italic from '@tiptap/extension-italic'
import Underline from '@tiptap/extension-underline'
import TextStyle from '@tiptap/extension-text-style'
import { Extension } from '@tiptap/core'

const FontSize = Extension.create({
  name: 'fontSize',
  addAttributes() {
    return {
      fontSize: {
        default: null,
        parseHTML: element => element.style.fontSize || null,
        renderHTML: attributes => {
          if (!attributes.fontSize) return {}
          return { style: `font-size: ${attributes.fontSize}` }
        },
      },
    }
  },
  addCommands() {
    return {
      setFontSize: fontSize => ({ chain }) => {
        return chain().setMark('textStyle', { fontSize }).run()
      },
    }
  },
})

const documentId = window.documentId

if (documentId) {
  const editor = new Editor({
    element: document.querySelector('#editor'),
    extensions: [
      StarterKit.configure({ history: true }),
      Bold, Italic, Underline, TextStyle, FontSize,
    ],
    content: window.initialContent || '<p>Mulai mengetik...</p>',
  })

  document.getElementById('bold')?.addEventListener('click', () => {
    editor.chain().focus().toggleBold().run()
  })

  document.getElementById('italic')?.addEventListener('click', () => {
    editor.chain().focus().toggleItalic().run()
  })

  document.getElementById('underline')?.addEventListener('click', () => {
    editor.chain().focus().toggleUnderline().run()
  })

  document.getElementById('fontSize')?.addEventListener('change', (e) => {
    editor.chain().focus().setFontSize(e.target.value).run()
  })

  editor.on('transaction', () => {
    document.getElementById('bold')?.classList.toggle('is-active', editor.isActive('bold'))
    document.getElementById('italic')?.classList.toggle('is-active', editor.isActive('italic'))
    document.getElementById('underline')?.classList.toggle('is-active', editor.isActive('underline'))
  })

  document.getElementById('saveVersion')?.addEventListener('click', async () => {
    const content = editor.getHTML()
    const note = prompt('Catatan untuk versi ini (opsional):', 'Versi ' + new Date().toLocaleTimeString())
    if (note === null) return

    try {
      const response = await fetch(`/documents/${documentId}/versions`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ content: content, note: note || 'Auto Save' })
      })
      const data = await response.json()
      alert('✅ ' + data.message)
    } catch (error) {
      alert('❌ Gagal menyimpan versi!')
    }
  })

  const modal = document.getElementById('historyModal')
  const closeModal = document.getElementById('closeModal')
  const openHistoryBtn = document.getElementById('openHistory')
  const versionList = document.getElementById('versionList')

  openHistoryBtn?.addEventListener('click', async () => {
    modal.style.display = 'block'
    await loadVersions()
  })

  closeModal?.addEventListener('click', () => {
    modal.style.display = 'none'
  })

  window.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none'
  })

  async function loadVersions() {
    versionList.innerHTML = '<li style="text-align:center; color:#666;">Memuat...</li>'
    try {
      const response = await fetch(`/documents/${documentId}/versions`)
      const versions = await response.json()
      
      if (versions.length === 0) {
        versionList.innerHTML = '<li style="text-align:center; color:#666;">Belum ada versi tersimpan.</li>'
        return
      }

      versionList.innerHTML = versions.map(version => `
        <li class="version-item">
          <div class="version-info">
            <div class="version-note">${version.note || 'Tanpa catatan'}</div>
            <div class="version-date">${new Date(version.created_at).toLocaleString('id-ID')}</div>
          </div>
          <div class="version-actions">
            <button class="btn-preview" onclick="previewVersion(${version.id})">👁️ Preview</button>
            <button class="btn-restore" onclick="restoreVersion(${version.id})">🔄 Restore</button>
          </div>
        </li>
      `).join('')
    } catch (error) {
      versionList.innerHTML = '<li style="text-align:center; color:red;">Gagal memuat riwayat.</li>'
    }
  }

  window.previewVersion = async (versionId) => {
    try {
      const response = await fetch(`/documents/${documentId}/versions/${versionId}`)
      const data = await response.json()
      const previewWindow = window.open('', '_blank')
      previewWindow.document.write(`<html><head><title>Preview</title><style>body{font-family:sans-serif;padding:20px;max-width:800px;margin:0 auto;line-height:1.6}</style></head><body>${data.content_html}</body></html>`)
      previewWindow.document.close()
    } catch (error) {
      alert('Gagal memuat preview!')
    }
  }

  window.restoreVersion = async (versionId) => {
    if (!confirm('⚠️ Yakin ingin mengembalikan dokumen ke versi ini?')) return
    
    try {
      const response = await fetch(`/documents/${documentId}/versions/${versionId}/restore`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      const data = await response.json()
      if (data.content) {
        editor.commands.setContent(data.content)
        alert('✅ Dokumen berhasil dikembalikan!')
        modal.style.display = 'none'
      }
    } catch (error) {
      alert('❌ Gagal restore versi!')
    }
  }
}