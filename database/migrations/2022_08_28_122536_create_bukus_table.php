<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBukusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bukus', function (Blueprint $table) {
            $table->id();
            $table->string('nib');
            $table->date('terima_tanggal');
            $table->text('judul');
            $table->string('id_kategori');
            $table->string('pengarang');
            $table->string('penerbit');
            $table->string('lokasi');
            $table->string('exp');
            $table->string('cnb');
            $table->string('asal_buku');
            $table->string('tempat_terbit');
            $table->string('stok');
            $table->string('ketersedian');
            $table->string('foto');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bukus');
    }
}
