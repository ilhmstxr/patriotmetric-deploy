(async () => {
    // 1. Fungsi pembantu untuk mengambil data Alpine.js secara robust & selektif
    const getAlpineData = () => {
        const elements = document.querySelectorAll('[x-data]');
        for (const el of elements) {
            let data = null;
            if (el.__x && el.__x.$data) {
                data = el.__x.$data;
            } else if (window.Alpine && typeof window.Alpine.$data === 'function') {
                data = window.Alpine.$data(el);
            } else if (el._x_dataStack) {
                data = el._x_dataStack[0];
            }
            
            // Mencari element x-data yang mengelola form verifikasi
            if (data && data.activeSection !== undefined && data.formData !== undefined && data.files !== undefined) {
                return data;
            }
        }
        return null;
    };

    const data = getAlpineData();
    if (!data) {
        console.error("%c[Error] Gagal menemukan element Alpine.js yang mengelola form verifikasi. Pastikan Anda berada di halaman verifikasi.", "color: #ff3333; font-weight: bold;");
        return;
    }

    console.log("%c[Start] Memulai pengisian otomatis form verifikasi...", "color: #1b5e20; font-weight: bold; font-size: 14px;");

    // 2. Isi Data Teks & Angka (formData)
    const mockData = {
        nama_pt: data.formData.nama_pt || "Universitas Patriot Metric",
        jenis_pt: data.formData.jenis_pt || "PTN",
        visi: "Menjadi Perguruan Tinggi Terkemuka dalam Penerapan Sistem Metrik Patriot secara Berkelanjutan.",
        misi: "1. Menyelenggarakan pendidikan metrik berkualitas tinggi dan berdaya saing nasional.\n2. Mengembangkan riset metrik berorientasi pengabdian masyarakat.",
        jumlah_fakultas: 5,
        jumlah_prodi: 20,
        jumlah_dosen: 350,
        jumlah_tendik: 120,
        jumlah_mahasiswa: 4500,
        jumlah_ormawa: 12,
        jumlah_ukm: 8,
        agama_islam: 3800,
        agama_kristen: 400,
        agama_katolik: 200,
        agama_hindu: 80,
        agama_buddha: 15,
        agama_konghucu: 5,
        nama_pic: data.formData.nama_pic || "Tester PIC",
        jabatan_pic: data.formData.jabatan_pic || "Kepala Pusat Metrik",
        no_hp_pic: data.formData.no_hp_pic || "081298765432",
        email_pic: data.formData.email_pic || "tester-pic@univ.ac.id"
    };

    Object.assign(data.formData, mockData);
    // Simpan ke local storage draft teks
    localStorage.setItem('verifikasi_draft', JSON.stringify(data.formData));
    console.log("   -> %cBerhasil mengisi data profil dan PIC.", "color: #16a34a;");

    // 3. Helper untuk membuat file mock (Blob) programmatically yang sesuai dengan magic bytes PDF dan valid PNG
    const createMockPdf = (filename) => {
        const pdfContent = "%PDF-1.4\n% artificial pdf content for testing";
        const blob = new Blob([pdfContent], { type: "application/pdf" });
        return new File([blob], filename, { type: "application/pdf" });
    };

    const createMockPng = (filename) => {
        const bytes = new Uint8Array([
            0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A, 0x00, 0x00, 0x00, 0x0D,
            0x49, 0x48, 0x44, 0x52, 0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x01,
            0x08, 0x06, 0x00, 0x00, 0x00, 0x1F, 0x15, 0xC4, 0x89, 0x00, 0x00, 0x00,
            0x0B, 0x49, 0x44, 0x41, 0x54, 0x78, 0x9C, 0x63, 0x00, 0x01, 0x00, 0x00,
            0x05, 0x00, 0x01, 0x0D, 0x0A, 0x2D, 0xB4, 0x00, 0x00, 0x00, 0x00, 0x49,
            0x45, 0x4E, 0x44, 0xAE, 0x42, 0x60, 0x82
        ]);
        const blob = new Blob([bytes], { type: "image/png" });
        return new File([blob], filename, { type: "image/png" });
    };

    // 4. Generate & Set Mock Files
    const mockFiles = {
        surat_pernyataan: createMockPdf("surat_pernyataan_mock.pdf"),
        sk_pendirian: createMockPdf("sk_pendirian_mock.pdf"),
        profil_pt: createMockPdf("profil_institusi_mock.pdf"),
        logo_url: createMockPng("logo_institusi_mock.png"),
        struktur_organisasi: createMockPdf("struktur_organisasi_mock.pdf")
    };

    console.log("   -> %cMembuat file bukti pdf/image mock...", "color: #0284c7;");

    await data.initDB();

    for (const field in mockFiles) {
        const file = mockFiles[field];
        data.files[field] = file;
        await data.saveFileToDB(field, file); // Save to IndexedDB draft

        // Generate URL preview
        data.previews[field] = URL.createObjectURL(file);
        console.log(`      - Berhasil upload mock file: ${file.name}`);
    }

    // 5. Pindahkan ke section akhir (Pratinjau) agar user bisa langsung review & submit
    data.activeSection = 4;
    localStorage.setItem('verifikasi_section', 4);

    console.log("%c[Success] Pengisian otomatis verifikasi selesai! Halaman dialihkan ke step Pratinjau.", "color: #1b5e20; font-weight: bold; font-size: 14px;");
})();
