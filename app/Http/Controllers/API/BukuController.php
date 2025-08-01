<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\KategoriBook;
use App\Models\Penerbit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{

    public function generateKodeBuku()
    {
        $prefix = 'BK-' . Carbon::now()->format('Ymd');

        // Ambil jumlah buku hari ini
        $countToday = Buku::whereDate('created_at', Carbon::today())->count() + 1;

        // Format menjadi tiga digit (001, 002, dst.)
        $number = str_pad($countToday, 3, '0', STR_PAD_LEFT);

        return $prefix . '-' . $number;
    }

    public function generateKodePenerbit()
    {
        $prefix = 'SP-' . Carbon::now()->format('Ymd');

        // Ambil jumlah buku hari ini
        $countToday = Buku::whereDate('created_at', Carbon::today())->count() + 1;

        // Format menjadi tiga digit (001, 002, dst.)
        $number = str_pad($countToday, 3, '0', STR_PAD_LEFT);

        return $prefix . '-' . $number;
    }

    public function list_data_penerbit()
    {
        $penerbit = Penerbit::select(['id', 'nama'])
            ->groupBy('id', 'nama')->orderBy('nama')->get();

        return response()->json([
            'message' => 'Sukses',
            'data_penerbit' => $penerbit,
            'success' => true,
        ], 200);
    }

    public function list_data_kategori()
    {
        $kategori = KategoriBook::select(['id', 'nama_kategori'])
            ->groupBy('id', 'nama_kategori')->orderBy('nama_kategori')->get();

        return response()->json([
            'message' => 'Sukses',
            'data_kategori' => $kategori,
            'success' => true,
        ], 200);
    }

    public function list_buku(Request $request)
    {
        $keyword = $request->get('keyword', null);
        $perPage = $request->get('per-page', 10);

        if ($perPage > 100) {
            $perPage = 100;
        }

        $model = Buku::select([
            'books.id',
            'books.kode_buku',
            'books.kategori',
            'books.nama_buku',
            'books.harga',
            'books.stok',
            'penerbits.nama as nama_penerbit',
        ])
            ->leftJoin('penerbits', 'books.penerbit_id', '=', 'penerbits.id');

        if (!empty($keyword)) {
            $model->where(function ($q) use ($keyword) {
                $q->where('books.kode_buku', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('books.kategori', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('books.nama_buku', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('penerbits.nama', 'LIKE', '%' . $keyword . '%');
            });
        }

        $book = $model->paginate($perPage);

        $book->appends(['per-page' => $perPage]);

        return response()->json([
            'message' => 'Sukses',
            'data_buku' => $book,
            'success' => true,
        ], 200);
    }

    public function create_buku(Request $request)
    {
        $customMessages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar di sistem',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'min' => ':attribute harus memiliki setidaknya :min karakter.',
            'regex' => ':attribute harus mengandung setidaknya satu huruf kapital dan satu angka.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus memiliki panjang antara :min dan :max digit.',
        ];

        $request->validate([
            'kategori' => 'required|max:225',
            'nama_buku' => 'required|max:225',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'penerbit_id' => 'required|exists:penerbits,id',
        ], $customMessages);

        $book = new Buku();
        $book->uuid = Str::uuid()->toString();
        $book->kode_buku = $this->generateKodeBuku();
        $book->kategori = $request->kategori;
        $book->nama_buku = $request->nama_buku;
        $book->harga = $request->harga;
        $book->stok = $request->stok;
        $book->penerbit_id = $request->penerbit_id;
        try {
            $book->save();
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Gagal menambah data buku' . $exception->getMessage(),
                'success' => false,
            ], 500);
        }

        unset($book->created_at, $book->updated_at);

        return response()->json([
            'message' => 'Data Buku berhasil ditambahkan',
            'data_buku' => $book,
            'success' => true,
        ], 200);
    }

    public function edit_buku(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
        ]);

        $book = Buku::where('id', '=', $request->id)->first();

        unset($book->created_at, $book->updated_at);

        if (!empty($book)) {
            return response()->json([
                'message' => 'Sukses',
                'data_buku' => $book,
                'success' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Data buku tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function update_buku(Request $request)
    {
        $customMessages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar di sistem',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'min' => ':attribute harus memiliki setidaknya :min karakter.',
            'regex' => ':attribute harus mengandung setidaknya satu huruf kapital dan satu angka.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus memiliki panjang antara :min dan :max digit.',
        ];

        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
            'kategori' => 'required',
            'nama_buku' => 'required',
            'harga' => 'required',
            'stok' => 'required',
            'penerbit_id' => 'required|exists:penerbits,id',
        ], $customMessages);

        $book = Buku::where('id', '=', $request->id)->first();

        if (!empty($book)) {
            try {
                $book->update([
                    'kategori' => $request->kategori,
                    'nama_buku' => $request->nama_buku,
                    'nama_lengkap' => $request->nama_lengkap,
                    'harga' => $request->harga,
                    'stok' => $request->stok,
                    'penerbit_id' => $request->penerbit_id,
                ]);
            } catch (\Exception $exception) {
                return response()->json([
                    'message' => 'Gagal mengupdate data buku' . $exception->getMessage(),
                    'success' => false,
                ], 500);
            }

            return response()->json([
                'message' => 'Data buku berhasil diupdate',
                'data_buku' => $book,
                'success' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Data tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function delete_buku(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
        ]);

        $book = Buku::where('id', '=', $request->id)
            ->first();

        if (!empty($book)) {
            try {
                $book = Buku::where('id', '=', $request->id)
                    ->delete();

                return response()->json([
                    'message' => 'Data buku berhasil dihapus',
                    'success' => true,
                ], 200);
            } catch (\Exception $exception) {
                return response()->json([
                    'message' => 'Gagal menghapus data buku' . $exception->getMessage(),
                    'success' => false,
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Data buku tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function list_penerbit(Request $request)
    {
        $keyword = $request->get('keyword', null);
        $perPage = $request->get('per-page', 10);

        if ($perPage > 100) {
            $perPage = 100;
        }

        $model = Penerbit::select([
            'id',
            'kode_penerbit',
            'nama',
            'alamat',
            'kota',
            'telepon',
        ]);

        if (!empty($keyword)) {
            $model->where(function ($q) use ($keyword) {
                $q->where('kode_penerbit', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('nama', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('alamat', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('kota', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('telepon', 'LIKE', '%' . $keyword . '%');
            });
        }

        $penerbit = $model->paginate($perPage);

        $penerbit->appends(['per-page' => $perPage]);

        return response()->json([
            'message' => 'Sukses',
            'data_penerbit' => $penerbit,
            'success' => true,
        ], 200);
    }

    public function create_penerbit(Request $request)
    {
        $customMessages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar di sistem',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'min' => ':attribute harus memiliki setidaknya :min karakter.',
            'regex' => ':attribute harus mengandung setidaknya satu huruf kapital dan satu angka.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus memiliki panjang antara :min dan :max digit.',
        ];

        $request->validate([
            'nama' => 'required|max:225',
            'alamat' => 'required|max:225',
            'kota' => 'required|max:225',
            'telepon' => 'required|max:225',
        ], $customMessages);

        $penerbit = new Penerbit();
        $penerbit->uuid = Str::uuid()->toString();
        $penerbit->kode_penerbit = $this->generateKodePenerbit();
        $penerbit->nama = $request->nama;
        $penerbit->alamat = $request->alamat;
        $penerbit->kota = $request->kota;
        $penerbit->telepon = $request->telepon;
        try {
            $penerbit->save();
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Gagal menambah data penerbit' . $exception->getMessage(),
                'success' => false,
            ], 500);
        }

        unset($penerbit->created_at, $penerbit->updated_at);

        return response()->json([
            'message' => 'Data Penerbit berhasil ditambahkan',
            'data_penerbit' => $penerbit,
            'success' => true,
        ], 200);
    }

    public function edit_penerbit(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
        ]);

        $penerbit = Penerbit::where('id', '=', $request->id)->first();

        unset($penerbit->created_at, $penerbit->updated_at);

        if (!empty($penerbit)) {
            return response()->json([
                'message' => 'Sukses',
                'data_penerbit' => $penerbit,
                'success' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Data penerbit tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function update_penerbit(Request $request)
    {
        $customMessages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar di sistem',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'min' => ':attribute harus memiliki setidaknya :min karakter.',
            'regex' => ':attribute harus mengandung setidaknya satu huruf kapital dan satu angka.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus memiliki panjang antara :min dan :max digit.',
        ];

        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
            'nama' => 'required',
            'alamat' => 'required',
            'kota' => 'required',
            'telepon' => 'required',
        ], $customMessages);

        $penerbit = Penerbit::where('id', '=', $request->id)->first();

        if (!empty($penerbit)) {
            try {
                $penerbit->update([
                    'nama' => $request->nama,
                    'alamat' => $request->alamat,
                    'nama_lengkap' => $request->nama_lengkap,
                    'kota' => $request->kota,
                    'telepon' => $request->telepon,
                ]);
            } catch (\Exception $exception) {
                return response()->json([
                    'message' => 'Gagal mengupdate data penerbit' . $exception->getMessage(),
                    'success' => false,
                ], 500);
            }

            return response()->json([
                'message' => 'Data penerbit berhasil diupdate',
                'data_penerbit' => $penerbit,
                'success' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Data tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function delete_penerbit(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
        ]);

        $penerbit = Penerbit::where('id', '=', $request->id)
            ->first();

        if (!empty($penerbit)) {
            try {
                $penerbit = Penerbit::where('id', '=', $request->id)
                    ->delete();

                return response()->json([
                    'message' => 'Data penerbit berhasil dihapus',
                    'success' => true,
                ], 200);
            } catch (\Exception $exception) {
                return response()->json([
                    'message' => 'Gagal menghapus data penerbit' . $exception->getMessage(),
                    'success' => false,
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Data penerbit tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function list_kategori(Request $request)
    {
        $keyword = $request->get('keyword', null);
        $perPage = $request->get('per-page', 10);

        if ($perPage > 100) {
            $perPage = 100;
        }

        $model = KategoriBook::select([
            'id',
            'nama_kategori',
        ]);

        if (!empty($keyword)) {
            $model->where(function ($q) use ($keyword) {
                $q->where('nama_kategori', 'LIKE', '%' . $keyword . '%');
            });
        }

        $kategori = $model->paginate($perPage);

        $kategori->appends(['per-page' => $perPage]);

        return response()->json([
            'message' => 'Sukses',
            'data_kategori' => $kategori,
            'success' => true,
        ], 200);
    }

    public function create_kategori(Request $request)
    {
        $customMessages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar di sistem',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'min' => ':attribute harus memiliki setidaknya :min karakter.',
            'regex' => ':attribute harus mengandung setidaknya satu huruf kapital dan satu angka.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus memiliki panjang antara :min dan :max digit.',
        ];

        $request->validate([
            'nama_kategori' => 'required|max:225',
        ], $customMessages);

        $kategori = new KategoriBook();
        $kategori->nama_kategori = $request->nama_kategori;
        try {
            $kategori->save();
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Gagal menambah data kategori' . $exception->getMessage(),
                'success' => false,
            ], 500);
        }

        unset($kategori->created_at, $kategori->updated_at);

        return response()->json([
            'message' => 'Data kategori berhasil ditambahkan',
            'data_kategori' => $kategori,
            'success' => true,
        ], 200);
    }

    public function edit_kategori(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
        ]);

        $kategori = KategoriBook::where('id', '=', $request->id)->first();

        unset($kategori->created_at, $kategori->updated_at);

        if (!empty($kategori)) {
            return response()->json([
                'message' => 'Sukses',
                'data_kategori' => $kategori,
                'success' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Data kategori tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function update_kategori(Request $request)
    {
        $customMessages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar di sistem',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'min' => ':attribute harus memiliki setidaknya :min karakter.',
            'regex' => ':attribute harus mengandung setidaknya satu huruf kapital dan satu angka.',
            'numeric' => ':attribute harus berupa angka.',
            'digits_between' => ':attribute harus memiliki panjang antara :min dan :max digit.',
        ];

        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
            'nama_kategori' => 'required',
        ], $customMessages);

        $kategori = KategoriBook::where('id', '=', $request->id)->first();

        if (!empty($kategori)) {
            try {
                $kategori->update([
                    'nama_kategori' => $request->nama_kategori,
                ]);
            } catch (\Exception $exception) {
                return response()->json([
                    'message' => 'Gagal mengupdate data kategori' . $exception->getMessage(),
                    'success' => false,
                ], 500);
            }

            return response()->json([
                'message' => 'Data kategori berhasil diupdate',
                'data_kategori' => $kategori,
                'success' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Data tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function delete_kategori(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric|digits_between:1,5',
        ]);

        $kategori = KategoriBook::where('id', '=', $request->id)
            ->first();

        if (!empty($kategori)) {
            try {
                $kategori = KategoriBook::where('id', '=', $request->id)
                    ->delete();

                return response()->json([
                    'message' => 'Data kategori berhasil dihapus',
                    'success' => true,
                ], 200);
            } catch (\Exception $exception) {
                return response()->json([
                    'message' => 'Gagal menghapus data kategori' . $exception->getMessage(),
                    'success' => false,
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Data kategori tidak ditemukan',
            'success' => false,
        ], 200);
    }

    public function list_pengadaan(Request $request)
    {
        $keyword = $request->get('keyword', null);
        $perPage = $request->get('per-page', 10);

        if ($perPage > 100) {
            $perPage = 100;
        }

        $model = Buku::select([
            'books.id',
            'books.nama_buku',

            'books.stok',
            'penerbits.nama as nama_penerbit',
        ])
            ->leftJoin('penerbits', 'books.penerbit_id', '=', 'penerbits.id')
            ->orderBy('books.stok', 'asc');

        if (!empty($keyword)) {
            $model->where(function ($q) use ($keyword) {
                $q->where('books.nama_buku', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('penerbits.nama', 'LIKE', '%' . $keyword . '%');
            });
        }

        $pengadaan = $model->paginate($perPage);

        $pengadaan->appends(['per-page' => $perPage]);

        return response()->json([
            'message' => 'Sukses',
            'data_pengadaan' => $pengadaan,
            'success' => true,
        ], 200);
    }

    public function report_pengadaan()
    {
        $data = DB::table('books')
            ->leftJoin('penerbits', 'books.penerbit_id', '=', 'penerbits.id')
            ->select('books.nama_buku', 'penerbits.nama as nama_penerbit', 'books.stok')
            ->orderBy('books.stok', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Laporan pengadaan buku dengan stok terendah',
            'data_pengadaan' => $data,
        ]);
    }
}
