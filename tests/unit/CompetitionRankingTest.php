<?php

namespace Tests\Unit;

use App\Controllers\Hitung;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;

final class CompetitionRankingTest extends CIUnitTestCase
{
    public function testNilaiSeriMenghasilkanCompetitionRank(): void
    {
        $reflection = new ReflectionClass(Hitung::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('assignCompetitionRanks');
        $method->setAccessible(true);

        $rows = $method->invoke($controller, [
            ['nama' => 'Budi', 'nis' => '2', 'nilai' => 0.8750001],
            ['nama' => 'Andi', 'nis' => '1', 'nilai' => 0.8750002],
            ['nama' => 'Citra', 'nis' => '3', 'nilai' => 0.82],
            ['nama' => 'Deni', 'nis' => '4', 'nilai' => 0.79],
        ]);

        $this->assertSame(['Andi', 'Budi', 'Citra', 'Deni'], array_column($rows, 'nama'));
        $this->assertSame([1, 1, 3, 4], array_column($rows, 'rank'));
    }
}
