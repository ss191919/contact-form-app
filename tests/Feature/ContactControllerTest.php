<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function お問い合わせ入力画面が表示できる(): void
    {
        $category = Category::factory()->create([
            'content' => '商品について',
        ]);

        $tag = Tag::factory()->create([
            'name' => '重要',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertViewIs('contact.index');

        $response->assertViewHas('categories', function ($categories) use ($category) {
            return $categories->contains($category);
        });

        $response->assertViewHas('tags', function ($tags) use ($tag) {
            return $tags->contains($tag);
        });

        $response->assertSee('商品について');
        $response->assertSee('重要');
    }

    /**
     * @test
     */
    public function お問い合わせ確認画面が表示できる(): void
    {
        $category = Category::factory()->create([
            'content' => '商品について',
        ]);
        $tag = Tag::factory()->create([
            'name' => '重要',
        ]);

        $response = $this->post('/contacts/confirm', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => '〇〇ビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('太郎');
        $response->assertSee('山田');
        $response->assertSee('test@example.com');
        $response->assertSee('商品について');
    }

    /**
     * @test
     */
    public function お問い合わせを送信できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->post('/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => '〇〇ビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'test@example.com',
        ]);

        $contact = Contact::first();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    /**
     * @test
     */
    public function お問い合わせ送信時にバリデーションエラーが返る(): void
    {
        $response = $this->post('/contacts', [
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid',
        ]);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'email',
        ]);
    }

    /**
     * @test
     */
    public function サンクスページが表示できる(): void
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);

        $response->assertViewIs('contact.thanks');
    }
}
