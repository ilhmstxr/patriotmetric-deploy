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
            
            // Mencari element x-data yang memiliki properti rubrik (categories atau allQuestions)
            if (data && (data.categories !== undefined || data.allQuestions !== undefined)) {
                return data;
            }
        }
        return null;
    };

    const data = getAlpineData();
    if (!data) {
        console.error("%c[Error] Gagal menemukan element Alpine.js yang mengelola form rubrik. Pastikan Anda berada di halaman /dashboard/rubrik.", "color: #ff3333; font-weight: bold;");
        return;
    }

    // Jika form sedang memuat dari API, tunggu sampai selesai loading
    if (data.loading) {
        console.log("%c[Info] Form rubrik sedang memuat data dari API. Menunggu selesai...", "color: #ffaa00; font-weight: bold;");
        while (data.loading) {
            await new Promise(resolve => setTimeout(resolve, 500));
        }
        console.log("%c[Info] Form selesai dimuat. Memulai pengisian...", "color: #1b5e20; font-weight: bold;");
    }

    const questions = data.allQuestions;
    if (!questions || questions.length === 0) {
        console.warn("%c[Warning] Tidak ada pertanyaan yang ditemukan di dalam form. Pastikan data rubrik telah berhasil dimuat dari server.", "color: #ffaa00; font-weight: bold;");
        return;
    }

    console.log(`%c[Start] Menemukan ${questions.length} pertanyaan. Mulai mengisi secara otomatis...`, "color: #1b5e20; font-weight: bold; font-size: 14px;");

    const driveUrl = "https://drive.google.com/drive/folders/1hoKCxerLRDa7coJq0YIIYcxUC0AhBmVG";

    for (let i = 0; i < questions.length; i++) {
        const q = questions[i];
        console.log(`%c[${i + 1}/${questions.length}] Memproses ${q.code} (ID: ${q.id}, Tipe: ${q.type})...`, "color: #0284c7; font-weight: bold;");

        // Set tautan bukti drive
        data.links[q.id] = driveUrl;

        // Set jawaban berdasarkan tipe input
        if (q.type === 'pilihan_ganda') {
            if (q.options && q.options.length > 0) {
                // Pilih opsi dengan nilai terbesar (maksimal)
                let maxOption = q.options[0];
                for (let j = 1; j < q.options.length; j++) {
                    const currentValue = parseFloat(q.options[j].value) || parseInt(q.options[j].id) || 0;
                    const maxValue = parseFloat(maxOption.value) || parseInt(maxOption.id) || 0;
                    if (currentValue > maxValue) {
                        maxOption = q.options[j];
                    }
                }
                data.answers[q.id] = maxOption.id;
                console.log(`   -> Memilih opsi dengan nilai maksimal: "${maxOption.text}"`);
            } else {
                console.warn(`   -> Opsi tidak ditemukan untuk pilihan ganda ${q.code}`);
            }
        } else if (q.type === 'isian_singkat') {
            if (q.code === 'B.13' || q.code === 'C.10') {
                // Format JSON khusus untuk B.13 dan C.10 (multi-scale)
                const isC10 = q.code === 'C.10';
                const baseVal = isC10 ? 50 : 25; // C.10 needs > 200 total, B.13 needs > 100 total
                const payload = {
                    lokal: { label: "Skala lokal / kota kabupaten / internal institusi", nilai: baseVal },
                    regional: { label: "Skala regional / provinsi", nilai: baseVal },
                    nasional: { label: "Skala nasional", nilai: baseVal },
                    internasional: { label: "Skala internasional", nilai: baseVal },
                    total_poin: baseVal * 1 + baseVal * 2 + baseVal * 3 + baseVal * 4 // 250 for B.13, 500 for C.10
                };
                data.answers[q.id] = JSON.stringify(payload);
                console.log(`   -> Mengisi isian skala ${q.code} dengan JSON maksimal (Total Poin: ${payload.total_poin}).`);
            } else {
                // Tentukan nilai maksimal dinamis untuk setiap kode soal isian_singkat
                let answerVal = 5; // Default fallback
                const prof = data.profil || {};
                
                switch (q.code) {
                    case 'B.1':
                    case 'B.9':
                        answerVal = 37; // > 36 bulan
                        break;
                    case 'B.2':
                        answerVal = 9; // > 8 SKS
                        break;
                    case 'B.3':
                    case 'B.4':
                    case 'B.5':
                    case 'B.10':
                    case 'B.16':
                    case 'B.19':
                    case 'C.1':
                        answerVal = 5; // > 4
                        break;
                    case 'B.6':
                        // > 100% dari jml_dosen
                        const dosen = parseInt(prof.jml_dosen) || 2300;
                        answerVal = Math.ceil(dosen * 1.1);
                        break;
                    case 'B.7':
                        // > 4% dari jml_prodi
                        const prodi = parseInt(prof.jml_prodi) || 40;
                        answerVal = Math.ceil(prodi * 0.05);
                        break;
                    case 'B.12':
                    case 'C.12':
                    case 'C.14':
                        answerVal = 15; // > 12
                        break;
                    case 'B.14':
                        answerVal = 105; // > 100
                        break;
                    case 'B.17':
                        answerVal = 9; // >= 9 ruangan
                        break;
                    case 'B.18':
                        // 100% dari jml_ormawa + jml_ukm
                        const ormawa = parseInt(prof.jml_ormawa) || 40;
                        const ukm = parseInt(prof.jml_ukm) || 20;
                        answerVal = ormawa + ukm;
                        break;
                    case 'B.20':
                        // 100% dari jml_agama_aktif
                        const activeReligions = parseInt(prof.jml_agama_aktif) || 4;
                        answerVal = activeReligions;
                        break;
                    case 'C.2':
                        // > jml_prodi
                        const pCount = parseInt(prof.jml_prodi) || 40;
                        answerVal = pCount + 10;
                        break;
                    case 'C.5':
                        // > 1% dari jml_mahasiswa
                        const mhs = parseInt(prof.jml_mahasiswa) || 23000;
                        answerVal = Math.ceil(mhs * 0.015);
                        break;
                    case 'C.7':
                        // 80% - 100% dari jml_mahasiswa
                        const mhsC7 = parseInt(prof.jml_mahasiswa) || 23000;
                        answerVal = Math.ceil(mhsC7 * 0.9);
                        break;
                    case 'C.8':
                        answerVal = 25; // 21-25 mahasiswa
                        break;
                    case 'C.9':
                        // > 4% dari jml_mahasiswa
                        const mhsC9 = parseInt(prof.jml_mahasiswa) || 23000;
                        answerVal = Math.ceil(mhsC9 * 0.05);
                        break;
                    case 'C.11':
                    case 'C.13':
                        answerVal = 25; // > 20
                        break;
                    default:
                        answerVal = 5;
                }
                
                data.answers[q.id] = answerVal;
                console.log(`   -> Mengisi nilai maksimal untuk ${q.code}: ${answerVal}`);
            }
        } else if (q.type === 'otomatis_sistem') {
            // Evaluasi otomatis sistem (seperti C.6)
            if (q.code === 'C.6') {
                data.answers[q.id] = "25"; // > 20 UKM untuk score 5
            } else if (typeof data.computeAutomatic === 'function') {
                data.answers[q.id] = data.computeAutomatic(q) || "25";
            } else {
                data.answers[q.id] = "25";
            }
            console.log(`   -> Mengisi otomatis sistem dengan nilai maksimal untuk ${q.code}: ${data.answers[q.id]}`);
        }

        // Kirim request penyimpanan ke API secara sequential (berurutan)
        if (typeof data.autoSave === 'function') {
            try {
                await data.autoSave(q.id);
                console.log(`   -> %cBerhasil tersimpan ke server.`, "color: #16a34a;");
            } catch (err) {
                console.error(`   -> %cGagal melakukan auto-save untuk ${q.code}:`, "color: #dc2626;", err);
            }
        } else if (typeof data.scheduleAutoSave === 'function') {
            data.scheduleAutoSave(q.id);
            console.log(`   -> Auto-save dijadwalkan.`);
        }

        // Delay 200ms per-pertanyaan agar request tidak bentrok (rate limiting / overload)
        await new Promise(resolve => setTimeout(resolve, 200));
    }

    console.log("%c[Success] Pengisian otomatis selesai! Seluruh isian telah tersimpan di server.", "color: #1b5e20; font-weight: bold; font-size: 14px;");
})();
