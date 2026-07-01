# 1. GET /api/peserta/steps

CHECK

**Kegunaan:** Mengambil daftar kategori (Stepper) dan status progres pengisian.

### Route -> Controller

- **Route:** `GET /api/peserta/steps` mengarah ke `PesertaPenugasanController@getSteps`.
- **Logic:** Mengidentifikasi `user_id` yang aktif dan mencari `penugasan_id` untuk periode berjalan.

### Controller -> DTO

Controller membuat `PenugasanContextDTO` yang berisi `penugasan_id` dan `user_id`.

### DTO -> Service

Memanggil `PesertaPenugasanService@getStepperProgress(PenugasanContextDTO $dto)`.

### Business Logic

Mengambil semua kategori, lalu menghitung perbandingan jumlah soal vs jumlah jawaban yang sudah di-submit untuk setiap kategori.

### Service -> Repository

Memanggil `CategoryRepository@getAllWithProgress($penugasanId)`.

**Eloquent:**

```php
Category::withCount(['questions', 'answers' => fn($q) => $q->where('penugasan_id', $id)])->get().
```

### Indikator Detail Implementasi

- **Auth Check:** Pastikan middleware `auth:sanctum` atau sejenisnya aktif untuk mendapatkan `Auth::id()`.
- **Active Period:** Query `penugasan_id` harus memfilter berdasarkan tahun saat ini dan status yang belum dipublikasikan.
- **Progress Calculation:** Hasil `withCount` harus dikonversi menjadi persentase atau status (misal: `completed` jika `questions_count == answers_count`).

---

# 2. GET /api/peserta/questions/{cat_id}

CHECK

**Kegunaan:** Mengambil soal kategori tertentu beserta jawaban yang sudah tersimpan (pre-filled).

### Route -> Controller

- Mengarah ke `PesertaPenugasanController@getQuestionsByCategory`.
- Menerima parameter `{cat_id}`.

### Controller -> DTO

Membuat `CategoryRequestDTO` (berisi `category_id` dan `penugasan_id`).

### DTO -> Service

Memanggil `PesertaPenugasanService@getQuestionsWithAnswers(CategoryRequestDTO $dto)`.

### Business Logic

Memastikan kategori valid dan mengambil relasi soal beserta opsi jawabannya.

### Service -> Repository

Memanggil `QuestionRepository@getByCategoryWithExistingAnswers($catId, $penugasanId)`.

**Eloquent:**

```php
Question::where('category_id', $catId)->with(['options', 'answers' => fn($q) => $q->where('penugasan_id', $id)])->get().
```

### Indikator Detail Implementasi

- **Pre-filling Data:** Pastikan `existing_answer` disertakan dalam response agar UI tidak kosong saat user kembali ke kategori sebelumnya.
- **Resource Mapping:** Gunakan Laravel API Resource untuk memformat `options` (pilihan ganda) agar mudah di-render di sisi frontend.
- **Category Validation:** Jika `cat_id` tidak ditemukan, kembalikan response `404 Not Found`.

---

# 3. POST /api/peserta/save-progress

CHECK
**Kegunaan:** Menyimpan jawaban (Klaim & Bukti) per kategori (Atomic Save).

### Route -> Controller

- Mengarah ke `PesertaPenugasanController@saveProgress`.
- **Validation:** `SaveProgressRequest` memvalidasi `category_id`, array `answers`, dan format `evidence_url`.

### Controller -> DTO

Mentransformasi request body menjadi `PesertaProgressDTO`.

### DTO -> Service

Memanggil `PesertaPenugasanService@persistProgress(PesertaProgressDTO $dto)`.

### Business Logic

-   - Cek status penugasan (Jika sudah finalize, lempar `403 Forbidden`).
- Melakukan pembersihan data (`Sanitization`) pada URL bukti.
- Menyiapkan data untuk operasi mass insert/update.

### Service -> Repository

Memanggil `PenugasanAnswerRepository@upsertAnswers(array $data)`.

**Eloquent:**

```php
DB::transaction menjalankan PenugasanAnswer::updateOrCreate untuk setiap indicator_id dalam array.
```

### Indikator Detail Implementasi

- **Atomic Persistence:** Gunakan `DB::beginTransaction()` and `DB::commit()` untuk menjamin jika satu jawaban gagal, seluruh progress kategori tersebut tidak rusak.
- **URL Validation:** Tambahkan validasi Regex pada `evidence_url` untuk memastikan input berupa link Google Drive yang valid.
- **Update or Create:** Pastikan `indicator_id + penugasan_id` menjadi kunci penentu apakah data akan di-update atau dibuat baru.

---

# 4. GET /api/peserta/preview-category/{cat_id}

CHECK
**Kegunaan:** Melihat estimasi skor kasar khusus untuk kategori yang baru diselesaikan.

### Route -> Controller

Mengarah ke `PesertaPenugasanController@getCategoryPreview.

### Controller -> DTO

Membuat `PreviewRequestDTO` (berisi `penugasan_id` dan `category_id`).

### DTO -> Service

Memanggil `ScoringService@calculateCategoryPreview(PreviewRequestDTO $dto)`.

### Business Logic

Mengambil semua `claim_value` di kategori tersebut, menjumlahkan berdasarkan bobot, dan mengonversi ke skala 1-5.

### Service -> Repository

Memanggil `PenugasanAnswerRepository@getAnswersByCategory($penugasanId, $catId)`.

**Eloquent:**

```php
PenugasanAnswer::whereHas('question', fn($q) => $q->where('category_id', $catId))->get().
```

### Indikator Detail Implementasi

- **Temporary Score:** Skor ini bersifat estimasi. Berikan labeling pada response seperti "Estimasi Skala 4".
- **Weighting Logic:** Pastikan Service memiliki akses ke bobot masing-masing kategori agar hitungan akurat sesuai rubrik.

---

# 5. POST /api/peserta/finalize

CHECK
**Kegunaan:** Mengunci seluruh data kuesioner (Final Lock).

### Route -> Controller

Mengarah ke `PesertaPenugasanController@finalize.

### Controller -> DTO

Membuat `FinalizePenugasanDTO` (berisi `penugasan_id`).

### DTO -> Service

Memanggil `PesertaPenugasanService@lockPenugasan(FinalizePenugasanDTO $dto)`.

### Business Logic

-   - Validasi kelengkapan: Cek apakah ada soal yang belum terjawab di semua kategori.
- Jika belum lengkap, lempar `422 Unprocessable Entity`.
- Jika lengkap, ubah status penugasans menjadi `SUBMITTED`.

### Service -> Repository

Memanggil `PenugasanRepository@updateStatus($id, 'SUBMITTED')`.

**Eloquent:**

```php
Penugasan::where('id', $id)->update(['status' => 'submitted', 'submitted_at' => now()]).
```

### Indikator Detail Implementasi

- **Completeness Check:** Lakukan pengecekan silang antara total pertanyaan di database dengan total entri di tabel `penugasan_answers`.
- **Immutable State:** Setelah status menjadi `SUBMITTED`, pastikan semua API POST (save) untuk `penugasan_id` tersebut mengembalikan error agar data tidak bisa dimanipulasi lagi.
- **Timestamping:** Simpan waktu penguncian di kolom `submitted_at` sebagai bukti audit waktu pengerjaan.
