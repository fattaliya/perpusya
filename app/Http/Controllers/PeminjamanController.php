<?php

namespace App\Http\Controllers;

use App\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Buku;
use DateTime;
use App\DataSiswa;

class PeminjamanController extends Controller
{
    public function read(){
        $peminjaman = DB::table('peminjamen')->orderBy('id','DESC')->get();
        return view('admin.peminjaman.index', ['peminjaman'=>$peminjaman]);
    }

    public function add(){
        $buku = buku::whereNotNull('id_kategori')->get();
        $data_siswa = DB::table('data_siswas')->orderBy('id','DESC')->get();
        return view('admin.peminjaman.tambah', ['buku'=>$buku,'data_siswa'=>$data_siswa]);
    }

    public function print_peminjaman()
    {
        $peminjaman = DB::table('peminjamen')->orderBy('id','DESC')->get();
        return view('admin/peminjaman/print_peminjaman', compact('peminjamen'));
    }

    public function create(Request $request)
    {

        // dd($request->all());die();


        $data = peminjaman::create([
            'id_siswa' => $request['id_siswa'],
            'tanggal_pinjam' =>$request['tanggal_pinjam'],
            'tanggal_kembali' => $request['tanggal_kembali'],
            'tanggal_pengembalian' => $request['tanggal_pengembalian'],
            'id_buku' => $request['id_buku'],
            'status_buku' => $request['status_buku'],
            'status_peminjaman' => $request['status_peminjaman'],

            // $data->save()
        ]);

            $sisa_stok = Buku::where('id',$data->id_buku)->get();
            foreach ($sisa_stok as $sisa) {
                $rest = Buku::find($sisa->id);
                $rest->where('id', $data->id_buku)
                ->update([
                    'ketersedian' => ($rest->ketersedian - 1),
                ]);

                $rest->save();
            }
        if($data){
        return redirect('/admin/peminjaman')->with("success","Data Berhasil Ditambah !");
    }
}

    public function edit($id){

        $peminjaman= DB::table('peminjamen')->where('id',$id)->first();
        $buku = DB::table('bukus')->find($peminjaman->id_buku);
        $bukuAll = DB::table('bukus')->where('id','!=',$buku->id)->orderBy('id','DESC')->get();

        $siswa = DB::table('data_siswas')->where('nama_siswa',$peminjaman->id)->first();
        $siswaAll = DB::table('data_siswas')->where('nama_siswa','!=',$peminjaman->id_siswa)->orderBy('nama_siswa','ASC')->get();

        return view('admin.peminjaman.edit',['peminjaman'=>$peminjaman,'buku'=>$buku,'bukuAll'=>$bukuAll,'siswa'=>$siswa,'siswaAll'=>$siswaAll]);
    }

    public function update(Request $request, $id) {


        // $data = peminjaman::update([


        //     'id_siswa' => $request['id_siswa'],
        //     'tanggal_pinjam' =>$request['tanggal_pinjam'],
        //     'tanggal_kembali' => $request['tanggal_kembali'],
        //     'tanggal_pengembalian' => $request['tanggal_pengembalian'],
        //     'id_buku' => $request['id_buku'],
        //     'status_buku' => $request['status_buku'],
        //     'status_peminjaman' => $request['status_peminjaman']

        // ]);

        // $sisa_stok = Buku::where('id',$data->id_buku)->get();
        // foreach ($sisa_stok as $sisa) {
        //     $rest = Buku::find($sisa->id);
        //     $rest->where('id', $data->id_buku)
        //     ->update([
        //         'ketersedian' => ($rest->ketersedian + 1),
        //     ]);

        //     $rest->save();
        // }
        // if($data){
        return redirect('/admin/peminjaman')->with("success","Data Berhasil Diupdate !");
    // }
}

    public function delete($id)
    {
        $peminjaman= DB::table('peminjamen')->where('id',$id)->first();
        DB::table('peminjamen')->where('id',$id)->delete();

        return redirect('/admin/peminjaman')->with("success","Data Berhasil Didelete !");
    }


    public function kembali($data)
    {
        // dd($data);die();
        // DB::table('peminjamen')
        // ->where('id', $id)
        // ->update([
        //     'tanggal_pengembalian' => date('Y-m-d'),
        // ]);

        // $post->update([
        //     'title'     => $request->title,
        //     'content'   => $request->content
        // ])->where('id',$id);
        $rest = Peminjaman::find($data);
        $buku = Buku::join('peminjamen','peminjamen.id_buku','=','bukus.id')
        ->select('bukus.ketersedian', 'bukus.stok')->where('peminjamen.id',$data)->get();
        foreach ($buku as $book){
            $ketersediaan = $book->ketersedian;
            $stok = $book->stok;
        }
        $buku_update = Buku::join('peminjamen','peminjamen.id_buku','=','bukus.id')
        ->select('bukus.ketersedian', 'bukus.stok')->where('peminjamen.id',$data)
        ->update([
            'bukus.ketersedian' => $ketersediaan + 1 ,
            'bukus.stok' => $stok + 1
        ]);
        // $update_book = $buku_update->save();
        // dd($ketersediaan);die();
        $rest->where('id', $data)
        ->update([
            'tanggal_pengembalian' =>date('Y-m-d'),
            'status_buku' => 'Aman',
            'status_peminjaman'=>'Telah Dikembalikan'
        ]);
        // $books->where()

       $restt = $rest->save();
        if($restt && $buku_update){

            return redirect('/admin/peminjaman/')->with("success","Buku Telah Di kembalikan !");
        }else{
            return redirect('/admin/peminjaman/')->with("gagal","Buku Telah Di kembalikan !");

        }


}


public function getdenda($id){
    // dd("jabkv");die();
    $peminjaman= DB::table('peminjamen')->where('id',$id)->first();
    $buku = DB::table('bukus')->where('id',$peminjaman->id_buku)->first();
    $denda = DB::table('kategoris')->where('id',$buku->id_kategori)->first();
    $tgl1 =$peminjaman->tanggal_kembali;
    $tgl11 = new DateTime($tgl1);
    $tgl2 = now();
    // $today = today();
    $tgl22 = $tgl2->format('Y-m-d');
    $interval = $tgl11->diff($tgl2);
    $days = $interval->format('%a');
    // (int)$selisih = $tgl2-$tgl1;
    // dd($tgl22);die();
    return view('admin.peminjaman.denda',['peminjaman'=>$peminjaman,'tgl'=>$tgl22,'buku'=>$buku,'denda'=>$denda,'days'=>$days]);


}
public function denda(Request $request){
    $bukuada = DB::table('bukus')->where('id',$request->id_buku)->first();
    // foreach ($bukuada as $book){
        //     $ketersediaan = $book->ketersedian;
        // }
        // dd($bukuada->ketersedian);die();
    DB::table('peminjamen')
    ->where('id', $request->id)
    ->update([
        'tanggal_pengembalian' => $request->tanggal_pengembalian,
        'status_peminjaman' => "Telah Dikembalikan",
        'status_buku' => "Telat",
    ]);
     DB::table('bukus')
    ->where('id', $request->id_buku)
    ->update([
        'ketersedian' => $bukuada->ketersedian + 1,
    ]);
    DB::table('dendas')
    ->insert([
        'id_peminjaman' => $request->id,
        'jumlah_denda' =>0,
        'total_denda' => $request->total_denda,
        'terlambat' => $request->telat,
    ]);
        return redirect('/admin/peminjaman')->with("success","Data Berhasil Diupdate !");


}
public function getKehilangan($id){
    // dd($id);die();

    $peminjaman= DB::table('peminjamen')->where('id',$id)->first();
    $buku = DB::table('bukus')->where('id',$peminjaman->id_buku)->first();
    $ganti = DB::table('kategoris')->where('id',$buku->id_kategori)->first();

    $tgl = now();
    // $today = today();
    $tgllapor = $tgl->format('Y-m-d');


    $siswa = DB::table('data_siswas')->where('nama_siswa',$peminjaman->id)->first();
    $siswaAll = DB::table('data_siswas')->where('nama_siswa','!=',$peminjaman->id_siswa)->orderBy('nama_siswa','ASC')->get();

    return view('admin.peminjaman.kehilangan',['peminjaman'=>$peminjaman,'buku'=>$buku,'tgllapor'=>$tgllapor,'kategori'=>$ganti]);
}
public function kehilangan(Request $request){
    if ($request->hasFile('foto')) {
        $foto = $request->file('foto');
            $name = time() . '.' . $foto->extension();
            $foto->move(public_path().'/foto/', $name);
    }else{
        $name = 'tidak ada file.png';
    }
    $bukuada = DB::table('bukus')->where('id',$request->id_buku)->first();
    DB::table('bukus')
    ->where('id', $request->id_buku)
    ->update([
        'stok' => $bukuada->stok -1
    ]);
    DB::table('peminjamen')
    ->where('id', $request->id)
    ->update([
        'tanggal_pengembalian' => $request->tanggal_lapor,
        'status_peminjaman' => "Telah Dilaporkan",
        'status_buku' => "Hilang",
    ]);
    if($request->kehilangan == "Buku" ||$request->kehilangan == "buku" ){
        $insert_pengganti = Buku::create([
            'nib'=>0,
            'id_kategori'=>0,
            'exp'=>0,
            'cnd'=>0,
            'foto'=>$name,
            'terima_tanggal' => $request->tanggal_lapor,
            'judul' => $request->pengganti,
            'pengarang' => $request->pengarang,
            'penerbit' => $request->penerbit,
            'tempat_terbit' => $request->tempat_terbit,
            'lokasi' => $request->lokasi,
            'asal_buku' =>"Ganti Rugi Buku Hilang",
            'stok' => 1,
            'ketersedian' =>1,
        ]);
        $insert_kehilangan = DB::table('kehilangans')->insert([
            'keterangan' => $request->alasan,
            'informasi' =>0,
            'id_penganti' => $insert_pengganti->id,
            'id_peminjaman' => $request->id,
        ]);

    }
    else if ($request->kehilangan == "Uang" ||$request->kehilangan == "uang"){
              $insert_kehilangan = DB::table('kehilangans')->insert([
            'keterangan' => $request->alasan,
            'informasi' =>$request->pengganti,
            'id_penganti' =>0,
            'id_peminjaman' => $request->id,
        ]);

    }
    return redirect('/admin/peminjaman')->with("success","Data Berhasil Diupdate !");

}


// public function denda($id){

//     $peminjaman= DB::table('peminjamen')->where('id',$id)->first();

//     $buku = DB::table('bukus')->find($peminjaman->id_buku);
//     $bukuAll = DB::table('bukus')->where('id','!=',$buku->id)->orderBy('id','DESC')->get();

//     $denda = DB::table('dendas')->find($peminjaman->id_denda);
//     $dendaAll = DB::table('dendas')->where('id','!=',$denda->id)->orderBy('id','DESC')->get();

//     $siswa = DB::table('data_siswas')->where('nama_siswa',$peminjaman->id)->first();
//     $siswaAll = DB::table('data_siswas')->where('nama_siswa','!=',$peminjaman->id_siswa)->orderBy('nama_siswa','ASC')->get();

//     return view('admin.peminjaman.denda',['peminjaman'=>$peminjaman,'buku'=>$buku, 'denda'=>$denda,'bukuAll'=>$bukuAll,'siswa'=>$siswa,'siswaAll'=>$siswaAll, 'dendaAll'=>$dendaAll]);
// }
}
