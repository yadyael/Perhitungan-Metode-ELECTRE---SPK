<?php
/**
 * ============================================================
 *  ELECTRE I + NILAI PHI — ENGINE PERHITUNGAN
 * ============================================================
 * Berisi seluruh logika perhitungan ELECTRE I beserta
 * fungsi bantu untuk menyimpan & membaca kembali setiap
 * tahapan dari database.
 *
 * Tahapan yang dihitung & disimpan:
 *   1. Matriks Keputusan      -> (tidak disimpan ulang, dari tbl_nilai)
 *   2. Normalisasi (R)        -> tbl_normalisasi
 *   3. Terbobot (V)            -> tbl_terbobot
 *   4. Concordance (C)        -> tbl_concordance
 *   5. Discordance (D)        -> tbl_discordance
 *   6. Dominan Concordance (F)-> tbl_dominan_concordance
 *   7. Dominan Discordance (G)-> tbl_dominan_discordance
 *   8. Agregat (E)             -> tbl_agregat
 *   9. Nilai Phi & Ranking     -> tbl_hasil
 *
 * Rumus:
 *   r_ij = x_ij / sqrt( Σ x_ij^2 )                (normalisasi per kolom)
 *   v_ij = r_ij * w_j                              (terbobot)
 *
 *   Untuk setiap pasangan (k, l), k != l, untuk setiap kriteria j:
 *     - jika tipe j = benefit -> k "lebih baik/sama" jika v_kj >= v_lj
 *     - jika tipe j = cost    -> k "lebih baik/sama" jika v_kj <= v_lj
 *   Jika "lebih baik/sama" -> j masuk himpunan Concordance C(k,l)
 *   Jika tidak              -> j masuk himpunan Discordance D(k,l)
 *
 *   c(k,l) = Σ w_j  untuk j ∈ C(k,l)
 *   d(k,l) = max|v_kj - v_lj| untuk j ∈ D(k,l)  /  max|v_kj - v_lj| untuk semua j
 *            (jika D(k,l) kosong atau penyebut 0 -> d(k,l) = 0)
 *
 *   c̄ = rata-rata seluruh c(k,l)
 *   d̄ = rata-rata seluruh d(k,l)
 *
 *   f(k,l) = 1 jika c(k,l) >= c̄, selain itu 0
 *   g(k,l) = 1 jika d(k,l) <= d̄, selain itu 0
 *   e(k,l) = f(k,l) * g(k,l)
 *
 *   Φ(k) = Σ_l e(k,l) - Σ_l e(l,k)
 *
 *   Ranking diurutkan dari Φ terbesar -> terkecil.
 * ============================================================
 */

/**
 * Jalankan seluruh perhitungan ELECTRE I + Nilai Phi,
 * lalu simpan setiap tahapan ke database.
 *
 * @param mysqli $conn
 * @return array ringkasan hasil perhitungan
 * @throws Exception jika data tidak valid / tidak lengkap
 */
function hitungElectre(mysqli $conn): array
{
    // Perhitungan untuk jumlah alternatif besar (±70+) butuh waktu lebih.
    set_time_limit(180);

    /* =========================================================
       1. AMBIL DATA DASAR (ALTERNATIF & KRITERIA)
    ========================================================= */
    $alternatif = [];
    $res = $conn->query("SELECT id, nama_daerah, provinsi FROM tbl_alternatif ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $alternatif[] = [
            'id'          => (int) $row['id'],
            'nama_daerah' => $row['nama_daerah'],
            'provinsi'    => $row['provinsi'],
        ];
    }

    $kriteria = [];
    $res = $conn->query("SELECT id, kode, nama_kriteria, bobot, tipe FROM tbl_kriteria ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $kriteria[] = [
            'id'            => (int) $row['id'],
            'kode'          => $row['kode'],
            'nama_kriteria' => $row['nama_kriteria'],
            'bobot'         => (float) $row['bobot'],
            // strtolower() -> jaga-jaga jika suatu saat ada data 'Benefit'/'BENEFIT'/'benefit' tercampur
            'tipe'          => strtolower(trim($row['tipe'])),
        ];
    }

    $m = count($alternatif); // jumlah alternatif (daerah)
    $n = count($kriteria);   // jumlah kriteria

    if ($m < 2) {
        throw new Exception("Minimal 2 alternatif (daerah) diperlukan untuk perhitungan ELECTRE.");
    }
    if ($n < 1) {
        throw new Exception("Minimal 1 kriteria diperlukan untuk perhitungan ELECTRE.");
    }

    /* =========================================================
       2. BANGUN MATRIKS KEPUTUSAN (X)
    ========================================================= */
    $X = []; // X[alternatif_id][kriteria_id] = nilai
    $res = $conn->query("SELECT alternatif_id, kriteria_id, nilai FROM tbl_nilai");
    while ($row = $res->fetch_assoc()) {
        $X[(int) $row['alternatif_id']][(int) $row['kriteria_id']] = (float) $row['nilai'];
    }

    // Validasi: semua alternatif harus memiliki nilai untuk SEMUA kriteria
    $belumLengkap = [];
    foreach ($alternatif as $alt) {
        foreach ($kriteria as $k) {
            if (!isset($X[$alt['id']][$k['id']])) {
                $belumLengkap[$alt['nama_daerah']] = true;
                break;
            }
        }
    }
    if (!empty($belumLengkap)) {
        throw new Exception(
            "Data nilai belum lengkap untuk: " . implode(', ', array_keys($belumLengkap)) .
            ". Lengkapi data tersebut pada halaman Data Nilai sebelum menghitung."
        );
    }

    /* =========================================================
       3. NORMALISASI (R)
       r_ij = x_ij / sqrt( Σ_i x_ij^2 )
    ========================================================= */
    $R = [];
    foreach ($kriteria as $k) {
        $sumSq = 0.0;
        foreach ($alternatif as $alt) {
            $sumSq += $X[$alt['id']][$k['id']] ** 2;
        }
        $denom = sqrt($sumSq);

        foreach ($alternatif as $alt) {
            $R[$alt['id']][$k['id']] = $denom > 0
                ? $X[$alt['id']][$k['id']] / $denom
                : 0.0;
        }
    }

    /* =========================================================
       4. MATRIKS TERNORMALISASI TERBOBOT (V)
       v_ij = r_ij * w_j
    ========================================================= */
    $V = [];
    foreach ($alternatif as $alt) {
        foreach ($kriteria as $k) {
            $V[$alt['id']][$k['id']] = $R[$alt['id']][$k['id']] * $k['bobot'];
        }
    }

    /* =========================================================
       5. CONCORDANCE (C) & DISCORDANCE (D)
    ========================================================= */
    $C = [];
    $D = [];

    foreach ($alternatif as $a1) {
        foreach ($alternatif as $a2) {
            if ($a1['id'] === $a2['id']) {
                continue; // diagonal tidak dihitung
            }

            $i = $a1['id'];
            $j = $a2['id'];

            $cSum            = 0.0;
            $discordKriteria = [];
            $maxDiffAll      = 0.0;

            foreach ($kriteria as $k) {
                $vk   = $V[$i][$k['id']];
                $vl   = $V[$j][$k['id']];
                $diff = abs($vk - $vl);

                if ($diff > $maxDiffAll) {
                    $maxDiffAll = $diff;
                }

                $lebihBaikAtauSama = ($k['tipe'] === 'benefit')
                    ? ($vk >= $vl)
                    : ($vk <= $vl);

                if ($lebihBaikAtauSama) {
                    $cSum += $k['bobot'];
                } else {
                    $discordKriteria[] = $k['id'];
                }
            }

            $C[$i][$j] = $cSum;

            if (empty($discordKriteria) || $maxDiffAll == 0.0) {
                $D[$i][$j] = 0.0;
            } else {
                $maxDiffDiscord = 0.0;
                foreach ($discordKriteria as $kid) {
                    $diff = abs($V[$i][$kid] - $V[$j][$kid]);
                    if ($diff > $maxDiffDiscord) {
                        $maxDiffDiscord = $diff;
                    }
                }
                $D[$i][$j] = $maxDiffDiscord / $maxDiffAll;
            }
        }
    }

    /* =========================================================
       6. THRESHOLD (RATA-RATA c̄ DAN d̄)
    ========================================================= */
    $sumC = 0.0;
    $sumD = 0.0;
    $pairCount = 0;

    foreach ($alternatif as $a1) {
        foreach ($alternatif as $a2) {
            if ($a1['id'] === $a2['id']) continue;
            $sumC += $C[$a1['id']][$a2['id']];
            $sumD += $D[$a1['id']][$a2['id']];
            $pairCount++;
        }
    }

    $cBar = $pairCount > 0 ? $sumC / $pairCount : 0.0;
    $dBar = $pairCount > 0 ? $sumD / $pairCount : 0.0;

    /* =========================================================
       7. MATRIKS DOMINAN F, G, & AGREGAT E
    ========================================================= */
    $F = [];
    $G = [];
    $E = [];

    foreach ($alternatif as $a1) {
        foreach ($alternatif as $a2) {
            if ($a1['id'] === $a2['id']) continue;

            $i = $a1['id'];
            $j = $a2['id'];

            $F[$i][$j] = ($C[$i][$j] >= $cBar) ? 1 : 0;
            $G[$i][$j] = ($D[$i][$j] <= $dBar) ? 1 : 0;
            $E[$i][$j] = ($F[$i][$j] && $G[$i][$j]) ? 1 : 0;
        }
    }

    /* =========================================================
       8. NILAI PHI (Φ)
       Φ(k) = Σ_l e(k,l) - Σ_l e(l,k)
    ========================================================= */
    $phi = [];
    foreach ($alternatif as $a1) {
        $ePlus  = 0; // baris -> seberapa sering k mendominasi
        $eMinus = 0; // kolom -> seberapa sering k didominasi

        foreach ($alternatif as $a2) {
            if ($a1['id'] === $a2['id']) continue;
            $ePlus  += $E[$a1['id']][$a2['id']];
            $eMinus += $E[$a2['id']][$a1['id']];
        }

        $phi[$a1['id']] = $ePlus - $eMinus;
    }

    /* =========================================================
       9. SIMPAN SELURUH HASIL KE DATABASE
    ========================================================= */
    $conn->begin_transaction();

    try {
        // Bersihkan hasil perhitungan sebelumnya (recalculate from scratch)
        $tabelHasil = [
            'tbl_normalisasi',
            'tbl_terbobot',
            'tbl_concordance',
            'tbl_discordance',
            'tbl_dominan_concordance',
            'tbl_dominan_discordance',
            'tbl_agregat',
            'tbl_hasil',
        ];
        foreach ($tabelHasil as $tabel) {
            if (!$conn->query("DELETE FROM {$tabel}")) {
                throw new Exception("Gagal membersihkan tabel {$tabel}: " . $conn->error);
            }
        }

        // --- Normalisasi (R) & Terbobot (V) ---
        $rowsR = [];
        $rowsV = [];
        foreach ($alternatif as $alt) {
            foreach ($kriteria as $k) {
                $rowsR[] = [$alt['id'], $k['id'], $R[$alt['id']][$k['id']]];
                $rowsV[] = [$alt['id'], $k['id'], $V[$alt['id']][$k['id']]];
            }
        }
        batchInsert($conn, 'tbl_normalisasi', ['alternatif_id', 'kriteria_id', 'nilai_r'], $rowsR);
        batchInsert($conn, 'tbl_terbobot',    ['alternatif_id', 'kriteria_id', 'nilai_v'], $rowsV);

        // --- Concordance, Discordance, F, G, E ---
        $rowsC = [];
        $rowsD = [];
        $rowsF = [];
        $rowsG = [];
        $rowsE = [];

        foreach ($alternatif as $a1) {
            foreach ($alternatif as $a2) {
                if ($a1['id'] === $a2['id']) continue;
                $i = $a1['id'];
                $j = $a2['id'];

                $rowsC[] = [$i, $j, $C[$i][$j]];
                $rowsD[] = [$i, $j, $D[$i][$j]];
                $rowsF[] = [$i, $j, $F[$i][$j]];
                $rowsG[] = [$i, $j, $G[$i][$j]];
                $rowsE[] = [$i, $j, $E[$i][$j]];
            }
        }

        batchInsert($conn, 'tbl_concordance',         ['alternatif_i', 'alternatif_j', 'nilai_concordance'], $rowsC);
        batchInsert($conn, 'tbl_discordance',         ['alternatif_i', 'alternatif_j', 'nilai_discordance'], $rowsD);
        batchInsert($conn, 'tbl_dominan_concordance', ['alternatif_i', 'alternatif_j', 'nilai_f'], $rowsF);
        batchInsert($conn, 'tbl_dominan_discordance', ['alternatif_i', 'alternatif_j', 'nilai_g'], $rowsG);
        batchInsert($conn, 'tbl_agregat',             ['alternatif_i', 'alternatif_j', 'nilai_e'], $rowsE);

        // --- Hasil Akhir: Nilai Phi & Ranking ---
        $items = [];
        foreach ($phi as $altId => $val) {
            $items[] = ['id' => $altId, 'phi' => $val];
        }
        // Urutkan Phi terbesar -> terkecil. Jika seri, urut berdasarkan id (stabil & deterministik).
        usort($items, function ($a, $b) {
            if ($a['phi'] == $b['phi']) {
                return $a['id'] <=> $b['id'];
            }
            return $b['phi'] <=> $a['phi'];
        });

        $rowsHasil = [];
        $rank = 1;
        foreach ($items as $it) {
            $rowsHasil[] = [$it['id'], $it['phi'], $rank];
            $rank++;
        }
        batchInsert($conn, 'tbl_hasil', ['alternatif_id', 'phi', 'ranking'], $rowsHasil);

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    return [
        'm'             => $m,
        'n'             => $n,
        'c_bar'         => $cBar,
        'd_bar'         => $dBar,
        'calculated_at' => date('Y-m-d H:i:s'),
    ];
}

/**
 * Insert banyak baris sekaligus menggunakan multi-row INSERT
 * untuk menghindari ribuan round-trip query satu per satu.
 * Aman digunakan karena seluruh nilai berasal dari hasil
 * komputasi server (bukan input user) yang sudah numerik.
 *
 * @param mysqli $conn
 * @param string $table
 * @param array  $columns
 * @param array  $rows     array of arrays, urutan sesuai $columns
 * @param int    $batchSize
 */
function batchInsert(mysqli $conn, string $table, array $columns, array $rows, int $batchSize = 300): void
{
    if (empty($rows)) {
        return;
    }

    $colList = implode(',', $columns);

    foreach (array_chunk($rows, $batchSize) as $chunk) {
        $valueGroups = [];

        foreach ($chunk as $row) {
            $formatted = array_map(function ($val) {
                if (is_int($val)) {
                    return (string) $val;
                }
                if (is_float($val)) {
                    // Format tetap menggunakan titik desimal, terlepas dari locale server.
                    return str_replace(',', '.', sprintf('%.10F', $val));
                }
                return $val;
            }, $row);

            $valueGroups[] = '(' . implode(',', $formatted) . ')';
        }

        $sql = "INSERT INTO {$table} ({$colList}) VALUES " . implode(',', $valueGroups);

        if (!$conn->query($sql)) {
            throw new Exception("Gagal menyimpan data ke {$table}: " . $conn->error);
        }
    }
}

/**
 * Cek status perhitungan terakhir.
 *
 * @param mysqli $conn
 * @return array ['has_result' => bool, 'updated_at' => string|null]
 */
function getElectreStatus(mysqli $conn): array
{
    $res = $conn->query("SELECT COUNT(*) AS jumlah, MAX(created_at) AS terakhir FROM tbl_hasil");
    $row = $res->fetch_assoc();

    return [
        'has_result' => (int) $row['jumlah'] > 0,
        'updated_at' => $row['terakhir'],
    ];
}

/**
 * Ambil matriks alternatif x kriteria dari tabel tertentu.
 * Digunakan untuk: Matriks Keputusan (tbl_nilai),
 * Normalisasi (tbl_normalisasi), Terbobot (tbl_terbobot).
 *
 * @return array [alternatif_id][kriteria_id] => float
 */
function pivotKriteriaMatrix(mysqli $conn, string $table, string $valueColumn): array
{
    $matrix = [];
    $res = $conn->query("SELECT alternatif_id, kriteria_id, {$valueColumn} AS val FROM {$table}");
    while ($row = $res->fetch_assoc()) {
        $matrix[(int) $row['alternatif_id']][(int) $row['kriteria_id']] = (float) $row['val'];
    }
    return $matrix;
}

/**
 * Ambil matriks pasangan alternatif (i x j) dari tabel tertentu.
 * Digunakan untuk: Concordance, Discordance, Dominan F/G, Agregat E.
 *
 * @return array [alternatif_i][alternatif_j] => float
 */
function pivotPairMatrix(mysqli $conn, string $table, string $valueColumn): array
{
    $matrix = [];
    $res = $conn->query("SELECT alternatif_i, alternatif_j, {$valueColumn} AS val FROM {$table}");
    while ($row = $res->fetch_assoc()) {
        $matrix[(int) $row['alternatif_i']][(int) $row['alternatif_j']] = (float) $row['val'];
    }
    return $matrix;
}

/**
 * Hitung ulang himpunan Concordance & Discordance untuk SATU pasangan
 * alternatif (i, j) — digunakan oleh viewer interaktif "Detail Himpunan".
 * Tidak butuh tabel tambahan karena dihitung langsung dari tbl_terbobot.
 *
 * @return array daftar baris: kode, nama_kriteria, tipe, bobot, v_i, v_j, himpunan (C/D)
 * @throws Exception jika data terbobot belum tersedia
 */
function getHimpunanCD(mysqli $conn, int $altI, int $altJ): array
{
    if ($altI === $altJ) {
        throw new Exception("Pilih dua daerah yang berbeda.");
    }

    $kriteria = [];
    $res = $conn->query("SELECT id, kode, nama_kriteria, bobot, tipe FROM tbl_kriteria ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $kriteria[(int) $row['id']] = [
            'kode'          => $row['kode'],
            'nama_kriteria' => $row['nama_kriteria'],
            'bobot'         => (float) $row['bobot'],
            'tipe'          => strtolower(trim($row['tipe'])),
        ];
    }

    if (empty($kriteria)) {
        throw new Exception("Belum ada data kriteria.");
    }

    $V = [];
    $stmt = $conn->prepare("SELECT kriteria_id, nilai_v FROM tbl_terbobot WHERE alternatif_id = ?");
    foreach ([$altI, $altJ] as $altId) {
        $stmt->bind_param("i", $altId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $V[$altId][(int) $row['kriteria_id']] = (float) $row['nilai_v'];
        }
    }

    if (empty($V[$altI]) || empty($V[$altJ])) {
        throw new Exception("Data matriks terbobot belum tersedia. Jalankan perhitungan ELECTRE terlebih dahulu.");
    }

    $hasil = [];
    foreach ($kriteria as $kid => $k) {
        $vi = $V[$altI][$kid] ?? 0.0;
        $vj = $V[$altJ][$kid] ?? 0.0;

        $lebihBaikAtauSama = ($k['tipe'] === 'benefit') ? ($vi >= $vj) : ($vi <= $vj);

        $hasil[] = [
            'kode'          => $k['kode'],
            'nama_kriteria' => $k['nama_kriteria'],
            'tipe'          => $k['tipe'],
            'bobot'         => $k['bobot'],
            'v_i'           => $vi,
            'v_j'           => $vj,
            'himpunan'      => $lebihBaikAtauSama ? 'C' : 'D',
        ];
    }

    return $hasil;
}

/**
 * Hitung himpunan Concordance C(i,j) dan Discordance D(i,j)
 * untuk SELURUH pasangan alternatif sekaligus (bukan hanya 1 pasangan).
 *
 * Dipakai untuk:
 *   - Accordion "Himpunan Concordance & Discordance" di halaman web
 *   - Sheet "Himpunan C & D" pada export Excel
 *
 * Tidak butuh tabel baru -> dihitung langsung dari tbl_terbobot + tbl_kriteria,
 * karena untuk jumlah alternatif yang wajar (puluhan) ini sangat cepat (<1 detik).
 *
 * @param mysqli $conn
 * @return array [
 *     'concordance' => [ ['i' => 1, 'j' => 2, 'kode_set' => ['C1','C2','C3']], ... ],
 *     'discordance' => [ ['i' => 1, 'j' => 2, 'kode_set' => ['C4','C5']], ... ],
 *     'alt_index'   => [alternatif_id => nomor_urut],
 * ]
 * @throws Exception jika data terbobot belum tersedia
 */
function getSemuaHimpunanCD(mysqli $conn): array
{
    $alternatif = [];
    $res = $conn->query("SELECT id, nama_daerah, provinsi FROM tbl_alternatif ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $alternatif[] = ['id' => (int) $row['id'], 'nama_daerah' => $row['nama_daerah']];
    }

    $kriteria = [];
    $res = $conn->query("SELECT id, kode, bobot, tipe FROM tbl_kriteria ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $kriteria[(int) $row['id']] = [
            'kode'  => $row['kode'],
            'bobot' => (float) $row['bobot'],
            'tipe'  => strtolower(trim($row['tipe'])),
        ];
    }

    if (empty($alternatif) || empty($kriteria)) {
        throw new Exception("Data alternatif atau kriteria belum tersedia.");
    }

    $V = pivotKriteriaMatrix($conn, 'tbl_terbobot', 'nilai_v');
    if (empty($V)) {
        throw new Exception("Data matriks terbobot belum tersedia. Jalankan perhitungan ELECTRE terlebih dahulu.");
    }

    $altIndex = [];
    foreach ($alternatif as $idx => $alt) {
        $altIndex[$alt['id']] = $idx + 1; // index dimulai dari 1, sesuai notasi A1, A2, ...
    }

    $concordance = [];
    $discordance = [];

    foreach ($alternatif as $a1) {
        foreach ($alternatif as $a2) {
            if ($a1['id'] === $a2['id']) continue;

            $i = $a1['id'];
            $j = $a2['id'];

            $setC = [];
            $setD = [];

            foreach ($kriteria as $kid => $k) {
                $vi = $V[$i][$kid] ?? 0.0;
                $vj = $V[$j][$kid] ?? 0.0;

                $lebihBaikAtauSama = ($k['tipe'] === 'benefit') ? ($vi >= $vj) : ($vi <= $vj);

                if ($lebihBaikAtauSama) {
                    $setC[] = $k['kode'];
                } else {
                    $setD[] = $k['kode'];
                }
            }

            $concordance[] = [
                'i'        => $altIndex[$i],
                'j'        => $altIndex[$j],
                'kode_set' => $setC,
            ];
            $discordance[] = [
                'i'        => $altIndex[$i],
                'j'        => $altIndex[$j],
                'kode_set' => $setD,
            ];
        }
    }

    return [
        'concordance' => $concordance,
        'discordance' => $discordance,
        'alt_index'   => $altIndex,
    ];
}

/**
 * Ambil seluruh hasil ranking beserta kategori prioritasnya.
 *
 * Aturan kategori:
 *   - Rank 1..5 (atau kurang dari 5 kalau total alternatif < 5) -> "Prioritas Utama" (selalu fix berdasarkan rank, bukan nilai)
 *   - Sisanya (rank > 5) dibagi 3 berdasarkan INTERVAL nilai Phi (bukan jumlah/persentil):
 *       rentang = Phi_maks - Phi_min (dihitung HANYA dari sisa daerah di luar top 5)
 *       posisi  = (Phi - Phi_min) / rentang   -> nilai 0..1
 *       posisi >= 2/3            -> "Prioritas Tinggi"
 *       posisi >= 1/3 dan < 2/3  -> "Prioritas Sedang"
 *       posisi < 1/3             -> "Cukup Baik"
 *
 * PENTING: karena berbasis interval nilai (bukan persentil), jumlah anggota
 * tiap kategori TIDAK DIJAMIN RATA. Kalau sebaran Phi mengelompok di satu area,
 * salah satu kategori bisa jauh lebih besar/kecil dari yang lain -- ini hasil
 * wajar dari metode interval, bukan bug.
 *
 * @param mysqli $conn
 * @return array daftar daerah dengan kategori, urut berdasarkan ranking ASC
 */
function getRankingWithKategori(mysqli $conn): array
{
    $res = $conn->query("
        SELECT h.alternatif_id, h.phi, h.ranking, a.nama_daerah, a.provinsi
        FROM tbl_hasil h
        JOIN tbl_alternatif a ON a.id = h.alternatif_id
        ORDER BY h.ranking ASC
    ");

    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;

    $total = count($rows);
    if ($total === 0) return [];

    $topN = min(5, $total); // jaga-jaga kalau total alternatif < 5

    // Kumpulkan Phi dari sisa daerah (rank > topN) untuk hitung rentang interval
    $sisaPhi = [];
    foreach ($rows as $r) {
        if ((int) $r['ranking'] > $topN) {
            $sisaPhi[] = (float) $r['phi'];
        }
    }

    $minPhi = !empty($sisaPhi) ? min($sisaPhi) : 0.0;
    $maxPhi = !empty($sisaPhi) ? max($sisaPhi) : 0.0;
    $rentang = $maxPhi - $minPhi;

    $kategoriMap = [
        'utama'  => ['label' => 'Prioritas Utama',  'color' => '#D9342B', 'bg' => 'rgba(217,52,43,0.12)'],
        'tinggi' => ['label' => 'Prioritas Tinggi',  'color' => '#E37434', 'bg' => 'rgba(227,116,52,0.12)'],
        'sedang' => ['label' => 'Prioritas Sedang',  'color' => '#D4A017', 'bg' => 'rgba(212,160,23,0.12)'],
        'cukup'  => ['label' => 'Cukup Baik',        'color' => '#4B9DA9', 'bg' => 'rgba(75,157,169,0.12)'],
    ];

    $hasil = [];
    foreach ($rows as $r) {
        $rank = (int) $r['ranking'];
        $phi  = (float) $r['phi'];

        if ($rank <= $topN) {
            $kat = 'utama';
        } elseif ($rentang <= 0.0) {
            // Semua Phi sisa nilainya sama -> tidak ada variasi untuk dibagi 3, default ke tengah.
            $kat = 'sedang';
        } else {
            $posisi = ($phi - $minPhi) / $rentang;
            if ($posisi >= 2 / 3) {
                $kat = 'tinggi';
            } elseif ($posisi >= 1 / 3) {
                $kat = 'sedang';
            } else {
                $kat = 'cukup';
            }
        }

        $hasil[] = [
            'alternatif_id'  => (int) $r['alternatif_id'],
            'ranking'        => $rank,
            'phi'            => $phi,
            'nama_daerah'    => $r['nama_daerah'],
            'provinsi'       => $r['provinsi'],
            'kategori'       => $kat,
            'kategori_label' => $kategoriMap[$kat]['label'],
            'kategori_color' => $kategoriMap[$kat]['color'],
            'kategori_bg'    => $kategoriMap[$kat]['bg'],
        ];
    }

    return $hasil;
}

/**
 * Ambil breakdown nilai mentah (skala 1-5) satu daerah per kriteria,
 * dibandingkan dengan rata-rata seluruh daerah untuk kriteria yang sama.
 * Dipakai sebagai "bukti nyata" alasan prioritas di halaman Ranking.
 *
 * @param mysqli $conn
 * @param int    $altId
 * @return array daftar kriteria dengan nilai, rata-rata, dan status (unggul/kurang)
 * @throws Exception jika data kriteria atau nilai belum tersedia
 */
function getKriteriaBreakdown(mysqli $conn, int $altId): array
{
    $kriteria = [];
    $res = $conn->query("SELECT id, kode, nama_kriteria, tipe FROM tbl_kriteria ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $kriteria[(int) $row['id']] = [
            'kode'          => $row['kode'],
            'nama_kriteria' => $row['nama_kriteria'],
            'tipe'          => strtolower(trim($row['tipe'])),
        ];
    }
    if (empty($kriteria)) {
        throw new Exception("Belum ada data kriteria.");
    }

    $nilaiAlt = [];
    $stmt = $conn->prepare("SELECT kriteria_id, nilai FROM tbl_nilai WHERE alternatif_id = ?");
    $stmt->bind_param("i", $altId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $nilaiAlt[(int) $row['kriteria_id']] = (float) $row['nilai'];
    }
    if (empty($nilaiAlt)) {
        throw new Exception("Data nilai untuk daerah ini belum tersedia.");
    }

    $rataRata = [];
    $res = $conn->query("SELECT kriteria_id, AVG(nilai) avg_val FROM tbl_nilai GROUP BY kriteria_id");
    while ($row = $res->fetch_assoc()) {
        $rataRata[(int) $row['kriteria_id']] = (float) $row['avg_val'];
    }

    $hasil = [];
    foreach ($kriteria as $kid => $k) {
        $nilai = $nilaiAlt[$kid] ?? 0.0;
        $rata  = $rataRata[$kid] ?? 0.0;

        // "Unggul" mempertimbangkan tipe kriteria: untuk cost, nilai LEBIH RENDAH itu unggul.
        $status = ($k['tipe'] === 'benefit')
            ? ($nilai >= $rata ? 'unggul' : 'kurang')
            : ($nilai <= $rata ? 'unggul' : 'kurang');

        $hasil[] = [
            'kode'          => $k['kode'],
            'nama_kriteria' => $k['nama_kriteria'],
            'tipe'          => $k['tipe'],
            'nilai'         => $nilai,
            'rata_rata'     => round($rata, 2),
            'status'        => $status,
        ];
    }

    return $hasil;
}
