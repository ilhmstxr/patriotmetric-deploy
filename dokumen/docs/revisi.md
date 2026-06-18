
- sk pertor di pengumuman : https://drive.google.com/file/d/1Hj5axqA6XB0HLB1Gz67cHMXSOH38y2Ph/view?usp=sharing


- nyari berita di web upn
berita

=== start of berita 1 ===
image asset di /public/assets/berita/1/2-cropped...webp
Menteri Pendidikan Tinggi, Sains, dan Teknologi (Mendiktisaintek) Brian Yuliarto menekankan pentingnya pemanfaatan bersama sumber daya pendidikan tinggi, khususnya laboratorium dan peralatan riset, agar utilisasinya semakin optimal. Hal tersebut disampaikan saat meresmikan Menara Wimaya (Twin Tower) Universitas Pembangunan Nasional (UPN) “Veteran” Jawa Timur, Senin (27/1).
Mendiktisaintek mendorong penguatan kolaborasi antarkampus, baik perguruan tinggi negeri maupun swasta. Menurutnya, praktik berbagi fasilitas dan sumber daya telah menjadi hal yang lazim di berbagai negara dan perlu terus diperkuat di Indonesia. “Gedungnya memang ada di sini, tetapi fasilitas dan laboratoriumnya tentu bisa dimanfaatkan juga oleh kampus-kampus lain. Riset dan pengajaran bisa kita lakukan bersama-sama,” ujar Menteri Brian.
image asset di /public/assets/berita/1/4-cropped...webp
Peresmian Menara Wimaya merupakan bagian dari kunjungan kerja Mendiktisaintek ke UPN “Veteran” Jawa Timur sekaligus bentuk apresiasi atas komitmen kampus dalam membangun fasilitas pendidikan tinggi secara mandiri dan berkelanjutan. Ia menilai pembangunan gedung tersebut sebagai capaian penting yang mencerminkan semangat kolegialitas dan kebersamaan antarkampus.
Selain infrastruktur, Mendiktisaintek juga mendorong pengembangan bahan ajar daring berbasis video yang dapat diakses secara luas sebagai implementasi praktik perguruan tinggi kelas dunia. “Dosen-dosen terbaik bisa membagikan ilmunya ke seluas-luasnya masyarakat, dan kampus-kampus di berbagai daerah dapat memanfaatkannya,” katanya.
Dalam kesempatan yang sama, Menteri Brian menegaskan bahwa keunggulan perguruan tinggi tidak hanya ditentukan oleh kemegahan infrastruktur, tetapi juga oleh lingkungan kampus yang tertata, bersih, dan memberikan layanan prima. Lingkungan tersebut dinilai penting dalam membentuk karakter, etos kerja, dan integritas mahasiswa sebagai calon sumber daya manusia unggul.
image asset di /public/assets/berita/1/3-cropped...webp
Rektor UPN “Veteran” Jawa Timur, Prof. Dr. Ir. Akhmad Fauzi, MMT.,IPU menyampaikan bahwa pembangunan Menara Wimaya merupakan bagian dari transformasi berkelanjutan kampus bela negara dalam memperkuat atmosfer akademik. “Sejalan dengan berkembangnya UPN ‘Veteran’ Jawa Timur, kami merasa perlu membangun fasilitas pendidikan yang mendukung integrasi akademik, riset, dan inovasi agar tercipta lingkungan belajar yang semakin kuat dan berdampak,” ujar Rektor.
Menara Wimaya dibangun secara multi-years selama tiga tahun oleh PT PP, dengan peletakan batu pertama pada 16 Juli 2022 dan rampung pada 2025. Gedung kembar seluas sekitar 29.000 meter persegi dan terdiri atas 13 lantai ini didanai melalui Penerimaan Negara Bukan Pajak (PNBP), serta dirancang sebagai simbol konektivitas dan kolaborasi antarbidang keilmuan melalui sky bridge yang menghubungkan kedua menara.
Selain peresmian Menara Wimaya, UPN “Veteran” Jawa Timur juga meluncurkan dua program strategis, yakni Patriot Metric University Ranking dan U-Bridge Program. Kedua program tersebut diposisikan sebagai penguat transformasi institusi, sejalan dengan pembangunan infrastruktur utama kampus dalam mendukung kebijakan Diktisaintek Berdampak dan upaya mencetak lulusan berkarakter, berdaya saing, serta berkontribusi nyata bagi bangsa.
image asset di /public/assets/berita/1/1-cropped...webp
=== end of berita 1===


Meningkatkan Kesadaran Bela Negara; Mendorong Perguruan Tinggi untuk mewujudkan dan meningkatkan karakter bela negara
Membangun Jejaring dan Kolaborasi Nasional; Membuka peluang bagi perguruan tinggi peserta menjadi bagian dari jejaring Patriot Metric yang memungkinkan kolaborasi di tingkat nasional.
Mendapatkan Pengakuan dan Reputasi; Meningkatkan citra dan reputasi perguruan tinggi sebagai kampus yang berkomitmen pada penguatan karakter bela negara.
Mendorong Perubahan dan Aksi Sosial; Perguruan tinggi dapat melakukan perubahan karakter dalam segala aspek Tridharma Perguruan Tinggi.

rubrik penilaian done
waktu ngesave pertanyaan masih error done


revisi 
done - view detail berita
done - testing halaman peserta


buatkan implementation tasknya terlebih dahulu
1. Fix namespace error in `app/Filament/Resources/BeritaResource.php` (import `EditAction` and `DeleteAction` from `Filament\Actions` instead of `Filament\Tables\Actions`).
2. Fix image path loading in `resources/views/welcome.blade.php`, `resources/views/berita.blade.php`, and `resources/views/berita-detail.blade.php` to handle both seeded paths (`assets/...`) and Filament uploaded paths (`berita/...`) dynamically.
3. Test and verify that `/admin/beritas`, `/berita`, and `/berita/{slug}` load without any errors.

DONE
- ngehapus kalender
- nambahin user peserta & reviewer ke seeder


CEK REVISI 08 - 06 - 2026
1. DONE image di berita di user & admin masih belum bisa di preview 
2. DONE di sisi dashboard admin, filamentnya masih ada
3. DONE rel-icon di admin masih belum di setting
4. DONE nambahin dockerfile & docker compose
5. DONE skor  rubrik penilaian [dihapus]
6. DONE filter asc desc, group by [dihapus]
7. DONE pagination minimal 10 [dirubah]
8. DONE logo patriotmetric sebelah kanan atas, balikin menjadi "patriotmetric" [dirubah]
9. DONE icon rubrik penilaian masih belum ada [ditambahkan]
10. DONE monitoring assessment diganti kata kata nya 


Poin 1: Widget 1 (Top Metrics Panel) - 3 Card Horizontal
Baris pertama akan diisi oleh tiga card utama yang disusun secara horizontal. Urutannya dari kanan ke kiri adalah:

Total Institusi: Menampilkan jumlah institusi, dengan logika backend yang memfilter data khusus untuk tahun berjalan (tahun saat ini).

Total Peserta 2026: Menampilkan jumlah total peserta khusus untuk periode 2026. Di dalam card ini, tambahkan sub-text yang menunjukkan rincian status verifikasi (misalnya: "X Aktif | Y Belum Verifikasi Email"), menggantikan kebutuhan card terpisah di bawahnya.

Total Reviewer: Menampilkan jumlah total reviewer yang berstatus aktif atau terdaftar pada sistem.

Poin 2: Widget 2 (Dynamic State Monitor) - Status Saat Ini
Baris kedua akan didedikasikan untuk satu banner card penuh (Full-width) yang berfungsi sebagai penunjuk fase assessment saat ini. Status ini bersifat dinamis dan akan berubah secara otomatis berdasarkan perbandingan waktu saat ini dengan Timeline Submission. Tiga kemungkinan statusnya adalah:

[PENGERJAAN] / Open: Aktif ketika waktu saat ini berada di antara tanggal "Submission Dibuka" dan "Submission Ditutup".

[REVIEWING] / Reviewer: Aktif ketika waktu saat ini telah melewati batas "Submission Ditutup" namun belum mencapai waktu "Pengumuman".

[PENGUMUMAN] / Published: Aktif ketika waktu saat ini telah mencapai atau melewati batas waktu "Pengumuman".

Poin 3: Penghapusan Elemen Tidak Relevan (The Purge)
Untuk mencapai Zero-Gap pada desain, elemen-elemen berikut harus dihapus atau disembunyikan dari dashboard utama karena redundan atau tidak relevan dengan metrik utama:

Teks panjang "Timeline Submission".

Card "Menunggu / Sedang Review", "Selesai Divalidasi", dan "Rata-rata Skor".

Card duplikat di baris bawah ("Total Peserta", "Belum Verifikasi Email", dan "Total Reviewer").

Card CMS ("Berita Published" & "Konten Compro") yang dipindahkan ke halaman terpisah.


[14.36, 18/6/2026] Strux: infokan hostingerna
[14.51, 18/6/2026] ragile Hidayatulloh: Revisi 12 Juni yang belum selesai:
1. Institusi telah memperoleh Bintang *sisa logo PT (ilham) 
2. Pengembang Panjang dan layout Timeline Perlu Di Dinamiskan di halaman welcome company profile
3. Tim diisi dengan nama dan role dengan format struktural asset
4. Nambah jenis perguruan tinggi PTKeagamaan *perlu dibongkar dalamnya 
5. Tambahkan hasil penilaian di detail peserta untuk admin dan reviewer 
6. Pedoman di compro masih belum karena masih belum final juga untuk isinya yang lain
7. Revisi yang rubrik masih belum difinalkan (ilham) 
8. institusi di marquee dimasukkan seeder (ilham) 
9. tanggal dimulai + force locok admin panel (ilham) 
10. bug setelah mengerjakan, lalu waktu ditutup tutup, lalu di sisi peserta juga masih disclaimer (status: reviewing). (ilham) 
11. timeline header catatan dihapus (ilham) 

Revisi 18 Juni 2026
1. Tombol Halaman Utama ikut ke scroll
2. Save Load Balancing perlu di cek dan testing (gausah di test)
3. Nambah grup WhatsApp Member lewat Email dan diberikan informasi di websitenya untuk informasinya 
4. Nama PIC di kanan atas dihapus
5. Nambah data login akses. nambah dummy di stock di halaman (halamn verifikasi, dashboard peserta) (ilham) 
6. Dimatikan pendaftarannya di website terlebih dahulu, di sisi registrasi nambahin status di (pakai timeline dibuka)(ilham) 
7. Tampilannya judulnya Panduan Patriot Metric dengan header sama dengan halaman lain serta kasih sidebar di utama dengan isi 1. panduan dan 2. pedoman dan kanannya isi kontennya