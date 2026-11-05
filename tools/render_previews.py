"""
Ubah setiap halaman PDF menjadi gambar pratinjau untuk fitur hover.

Pemakaian (dijalankan sekali di komputer Anda, bukan di server):
    pip install pymupdf
    python tools/render_previews.py  <folder-berisi-PDF>  public/previews

Hasil:
    public/previews/1_7_Standar_Pengelolaan_Pembelajaran/p1.jpg, p2.jpg, ...
Nama file PDF (tanpa .pdf) harus sama dengan nama dokumen di config/dokumen.php.
"""
import os
import sys

import fitz  # PyMuPDF

if len(sys.argv) != 3:
    sys.exit("Pemakaian: python render_previews.py <folder_pdf> <folder_output>")

src_dir, out_dir = sys.argv[1], sys.argv[2]

for filename in sorted(os.listdir(src_dir)):
    if not filename.lower().endswith(".pdf"):
        continue

    name = os.path.splitext(filename)[0]
    target = os.path.join(out_dir, name)
    os.makedirs(target, exist_ok=True)

    doc = fitz.open(os.path.join(src_dir, filename))
    for number, page in enumerate(doc, start=1):
        pix = page.get_pixmap(matrix=fitz.Matrix(1.0, 1.0))  # lebar ~600 px untuk A4
        pix.save(os.path.join(target, f"p{number}.jpg"), jpg_quality=70)

    print(f"{name}: {len(doc)} halaman")
    doc.close()