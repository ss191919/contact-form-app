<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function 管理画面が表示できる(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
    }

    /**
     * @test
     */
    public function 未認証ユーザーは管理画面にアクセスできない(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function お問い合わせ詳細が表示できる(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'content' => '商品について',
        ]);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)
            ->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);

        $response->assertViewIs('admin.show');

        $response->assertSee('商品について');
    }

    /**
     * @test
     */
    public function お問い合わせを削除できる(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)
            ->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    /**
     * @test
     */
    public function キーワード検索ができる(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $targetContact = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?keyword=山田');

        $response->assertStatus(200);

        $response->assertViewHas('contacts', function ($contacts) use ($targetContact) {
            return $contacts->contains($targetContact);
        });
    }

    /**
     * @test
     */
    public function 性別検索ができる(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $targetContact = Contact::factory()->create([
            'category_id' => $category->id,
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'gender' => 2,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?gender=1');

        $response->assertStatus(200);

        $response->assertViewHas('contacts', function ($contacts) use ($targetContact) {
            return $contacts->contains($targetContact);
        });
    }

    /**
     * @test
     */
    public function カテゴリ検索ができる(): void
    {
        $user = User::factory()->create();

        $targetCategory = Category::factory()->create();

        $otherCategory = Category::factory()->create();

        $targetContact = Contact::factory()->create([
            'category_id' => $targetCategory->id,
        ]);

        Contact::factory()->create([
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->actingAs($user)
            ->get("/admin?category_id={$targetCategory->id}");

        $response->assertStatus(200);

        $response->assertViewHas('contacts', function ($contacts) use ($targetContact) {
            return $contacts->contains($targetContact);
        });
    }

    /**
     * @test
     */
    public function 日付検索ができる(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $targetContact = Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-07-17 10:00:00',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-07-16 10:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?date=2026-07-17');

        $response->assertStatus(200);

        $response->assertViewHas('contacts', function ($contacts) use ($targetContact) {
            return $contacts->contains($targetContact);
        });
    }

    /**
     * @test
     */
    public function お問い合わせ一覧が7件ごとにページネーションされる(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        Contact::factory()
            ->count(8)
            ->create([
                'category_id' => $category->id,
            ]);

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->perPage() === 7;
        });
    }

    /**
     * @test
     */
    public function タグ編集画面が表示できる(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => '重要',
        ]);

        $response = $this->actingAs($user)
            ->get("/admin/tags/{$tag->id}/edit");

        $response->assertStatus(200);

        $response->assertViewIs('admin.tags.edit');
    }

    /**
     * @test
     */
    public function タグを作成できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/tags', [
            'name' => '新しいタグ',
        ]);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', [
            'name' => '新しいタグ',
        ]);
    }

    /**
     * @test
     */
    public function タグを更新できる(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => '古いタグ',
        ]);

        $response = $this->actingAs($user)
            ->put("/admin/tags/{$tag->id}", [
                'name' => '新しいタグ',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'name' => '新しいタグ',
        ]);
    }

    /**
     * @test
     */
    public function タグを削除できる(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    /**
     * @test
     */
    public function 未認証ユーザーはタグ操作できない(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->post('/admin/tags', [
            'name' => 'タグ',
        ]);

        $response->assertRedirect('/login');

        $response = $this->get("/admin/tags/{$tag->id}/edit");

        $response->assertRedirect('/login');

        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => '更新タグ',
        ]);

        $response->assertRedirect('/login');

        $response = $this->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/login');
    }
}
