<?php

namespace Tests\Unit;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function 正常なお問い合せ内容はバリデーションを通過する(): void
    {
        $category = Category::factory()->create();

        $tag = Tag::factory()->create();

        $request = new ContactRequest;

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => '〇〇ビル',
            'category_id' => 1,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ],
            $request->rules()
        );
        $this->assertFalse($validator->fails());
    }

    /**
     * @test
     */
    public function 不正な性別値はバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $request = new ContactRequest;

        $validator = Validator::make(
            [
                'first_name' => '太郎',
                'last_name' => '山田',
                'gender' => 4,
                'email' => 'test@example.com',
                'tel' => '09012345678',
                'address' => '東京都渋谷区',
                'category_id' => $category->id,
                'detail' => 'お問い合せ内容です。',
            ],
            $request->rules()
        );
        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'gender',
            $validator->errors()->toArray()
        );
    }

    /**
     * @test
     */
    public function 不正な電話番号形式はバリデーションエラーになる(): void
    {
        $request = new ContactRequest;

        $validator = Validator::make(
            [
                'first_name' => '太郎',
                'last_name' => '山田',
                'gender' => 1,
                'email' => 'test@example.com',
                'tel' => '090-1234-5678',
                'address' => '東京都渋谷区',
                'category_id' => 1,
                'detail' => 'お問い合わせ内容です。',
                'tag_ids' => [1],
            ],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
    }
}
