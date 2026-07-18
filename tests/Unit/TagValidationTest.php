<?php

namespace Tests\Unit;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class TagValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function タグ名は必須である(): void
    {
        $validator = Validator::make(
            [
                'name' => '',
            ],
            [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:tags,name',
                ],
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    /**
     * @test
     */
    public function タグ名は50文字以内である(): void
    {
        $validator = Validator::make(
            [
                'name' => str_repeat('あ', 51),
            ],
            [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:tags,name',
                ],
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    /**
     * @test
     */
    public function タグ名の重複は登録できない(): void
    {
        Tag::factory()->create([
            'name' => '重要',
        ]);

        $validator = Validator::make(
            [
                'name' => '重要',
            ],
            [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:tags,name',
                ],
            ]
        );

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function タグ更新時に自身の名前は許可される(): void
    {
        $tag = Tag::factory()->create([
            'name' => '重要',
        ]);

        $validator = Validator::make(
            [
                'name' => '重要',
            ],
            [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('tags', 'name')->ignore($tag->id),
                ],
            ]
        );

        $this->assertFalse($validator->fails());
    }

    /**
     * @test
     */
    public function タグ更新時に他のタグ名は使用できない(): void
    {
        Tag::factory()->create([
            'name' => '重要',
        ]);

        $tag = Tag::factory()->create([
            'name' => '確認',
        ]);

        $validator = Validator::make(
            [
                'name' => '重要',
            ],
            [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('tags', 'name')->ignore($tag->id),
                ],
            ]
        );

        $this->assertTrue($validator->fails());
    }
}
