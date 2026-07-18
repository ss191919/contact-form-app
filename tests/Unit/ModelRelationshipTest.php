<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function カテゴリから複数のお問い合わせを取得できる(): void
    {
        $category = Category::factory()->create();

        $contact1 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contacts = $category->contacts;

        $this->assertTrue($contacts->contains($contact1));
        $this->assertTrue($contacts->contains($contact2));
    }

    /**
     * @test
     */
    public function お問い合わせはカテゴリに属しタグを同期できる(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $contact->tags()->sync([
            $tag1->id,
            $tag2->id,
        ]);

        $this->assertEquals(
            $category->id,
            $contact->category->id,
        );

        $this->assertTrue(
            $contact->tags->contains($tag1)
        );

        $this->assertTrue(
            $contact->tags->contains($tag2)
        );
    }

    /**
     * @test
     */
    public function タグから複数のお問い合わせを取得できる(): void
    {
        $tag = Tag::factory()->create();

        $category = Category::factory()->create();

        $contact1 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag->contacts()->attach([
            $contact1->id,
            $contact2->id,
        ]);

        $contacts = $tag->contacts;

        $this->assertTrue($contacts->contains($contact1));
        $this->assertTrue($contacts->contains($contact2));
    }
}
