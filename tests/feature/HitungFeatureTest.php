<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\HitungMinimalSeeder;

/**
 * @internal
 */
final class HitungFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $seed = HitungMinimalSeeder::class;

    public function testHitungPageLoadsWithCalculatedContent(): void
    {
        $result = $this->withSession(['logged_in' => true, 'role' => 'admin'])->get('/hitung');

        $result->assertStatus(200);
        $result->assertSee('Detail Perhitungan');
        $result->assertSee('KOMPARASI AKHIR');
        $result->assertSee('SEBELUM–SESUDAH');
        $result->assertSee('Ranking Awal Test');
        $result->assertSee('Ani');
        $result->assertSee('Budi');
    }

    public function testHitungRedirectsToAhpWhenWeightIsInvalid(): void
    {
        db_connect()->table('kriteria')->update(['bobot' => 0.1], ['id_kriteria' => 1]);

        $result = $this->withSession(['logged_in' => true, 'role' => 'admin'])->get('/hitung');

        $result->assertStatus(302);
        $this->assertStringContainsString('/ahp', $result->response()->getHeaderLine('Location'));
    }

    public function testHitungShowsWarningWhenAlternatifOrPenilaianIsEmpty(): void
    {
        $db = db_connect();
        $db->table('penilaian')->truncate();
        $db->table('alternatif')->truncate();

        $result = $this->withSession(['logged_in' => true, 'role' => 'admin'])->get('/hitung');

        $result->assertStatus(200);
        $result->assertSee('Data Siswa atau Penilaian masih kosong.');
    }

    public function testAwardSubcriteriaAreAggregatedOnSave(): void
    {
        $result = $this->withSession(['logged_in' => true, 'role' => 'admin'])->post('/penilaian/save', [
            'id_alternatif' => 1,
            'nilai' => [1 => 90, 2 => 2],
            'penghargaan' => [
                'kabupaten' => 1,
                'provinsi' => 1,
                'nasional' => 1,
                'internasional' => 1,
            ],
        ]);

        $result->assertStatus(302);
        $c5 = db_connect()->table('penilaian')->where(['id_alternatif' => 1, 'id_kriteria' => 3])->get()->getRowArray();
        $this->assertSame(15.0, (float) $c5['nilai']);
        $detail = db_connect()->table('penilaian_penghargaan')->where('id_alternatif', 1)->get()->getRowArray();
        $this->assertSame(1, (int) $detail['internasional']);
    }
}
