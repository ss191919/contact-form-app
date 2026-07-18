<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 正しいフィルタ条件はバリデーションを通過する()
    {
        $category = Category::factory()->create();

        $validator = Validator::make(
            [
                'keyword' => 'テスト',
                'gender' => 1,
                'category_id' => $category->id,
                'date' => '2026-07-18',
            ],
            [
                'keyword' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'gender' => [
                    'nullable',
                    'integer',
                    'in:0,1,2,3',
                ],
                'category_id' => [
                    'nullable',
                    'integer',
                    'exists:categories,id',
                ],
                'date' => [
                    'nullable',
                    'date',
                ],
            ]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 不正な性別値はバリデーションエラーになる()
    {
        $validator = Validator::make(
            [
                'gender' => 5,
            ],
            [
                'gender' => [
                    'nullable',
                    'integer',
                    'in:0,1,2,3',
                ],
            ]
        );

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 存在しないカテゴリ_i_dはバリデーションエラーになる()
    {
        $validator = Validator::make(
            [
                'category_id' => 999,
            ],
            [
                'category_id' => [
                    'nullable',
                    'integer',
                    'exists:categories,id',
                ],
            ]
        );

        $this->assertTrue($validator->fails());
    }
}
