<?php

namespace Tests\Unit;

use App\Services\TaxCalculator;
use InvalidArgumentException;
use Tests\TestCase;

class TaxCalculatorTest extends TestCase
{
    public function test_税率別に集計して内税額を計算できる(): void
    {
        $calculator = new TaxCalculator;

        $result = $calculator->calculate(
            [
                ['subtotal' => 2160, 'tax_rate' => 8],
                ['subtotal' => 1080, 'tax_rate' => 8],
                ['subtotal' => -540, 'tax_rate' => 8],
            ],
            shippingFee: 1100,
            codFee: 330
        );

        $this->assertSame([
            [
                'tax_rate' => 8,
                'tax_included_amount' => 2700,
                'tax_amount' => 200,
            ],
            [
                'tax_rate' => 10,
                'tax_included_amount' => 1430,
                'tax_amount' => 130,
            ],
        ], $result);
    }

    public function test_税額の端数を税率ごとに一度だけ切り捨てる(): void
    {
        $calculator = new TaxCalculator;

        $result = $calculator->calculate([
            ['subtotal' => 100, 'tax_rate' => 8],
            ['subtotal' => 100, 'tax_rate' => 8],
        ]);

        $this->assertSame(14, $result[0]['tax_amount']);
    }

    public function test_対象額がゼロなら集計を作らない(): void
    {
        $calculator = new TaxCalculator;

        $result = $calculator->calculate([
            ['subtotal' => 500, 'tax_rate' => 8],
            ['subtotal' => -500, 'tax_rate' => 8],
        ]);

        $this->assertSame([], $result);
    }

    public function test_未対応の税率はエラーにする(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new TaxCalculator)->calculate([
            ['subtotal' => 1000, 'tax_rate' => 5],
        ]);
    }

    public function test_値引きが対象額を超えたらエラーにする(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new TaxCalculator)->calculate([
            ['subtotal' => 1000, 'tax_rate' => 8],
            ['subtotal' => -1500, 'tax_rate' => 8],
        ]);
    }
}
