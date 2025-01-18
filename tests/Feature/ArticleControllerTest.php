<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    /**
     * Test status 200
     * Test invalid category
     * Test paginated structure
     * 
     * @return void
     */
    public function testIndexArticle(): void
    {
        $response = $this->json('GET', "api/articles");
        $response->assertOk();

        $non_existing_category_id = 9999999;
        $response = $this->json('GET', "api/articles", [
            'categories' => [$non_existing_category_id]
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'The selected categories.0 is invalid.',
            'exception' => 'Illuminate\\Validation\\ValidationException',
        ]);


        $response = $this->json('GET', "api/articles", ['perPage' => 1]);
        $response->assertOk();

        $response->assertJsonStructure($this->getPaginatedStructure());
    }

    /**
     * Test admin can delete (soft delete) an article
     * 
     * @return void
     */
    public function testAdminCanDeleteArticle(): void
    {
        $admin_user = User::factory()->createOne();
        $admin_user->assignRole(Role::SUPER_ADMIN_ROLE->value);
        $this->actingAs($admin_user);
       
        $article = Article::factory()->createOne();
        
        $response = $this->json('DELETE', "api/articles/{$article->id}");
        $response->assertOk();

        $this->assertSoftDeleted($article);
    }

    /**
     * Test operator can't delete (soft delete) an article
     * 
     * @return void
     */
    public function testOperatorCantDeleteArticle(): void
    {
        $admin_user = User::factory()->createOne();
        $admin_user->assignRole(Role::OPERATION_ROLE->value);
        $this->actingAs($admin_user);
       
        $article = Article::factory()->createOne();
        
        $response = $this->json('DELETE', "api/articles/{$article->id}");
        $response->assertStatus(400);

        $response->assertJson([
            'success' => false,
            'message' => 'User does not have the right roles.',
            'exception' => 'Spatie\\Permission\\Exceptions\\UnauthorizedException',
        ]);
    }


    /**
     * Test admin can store an article
     * Test cant create an article with the same name and category
     * 
     * @return void
     */
    public function testAdminCanStoreArticle(): void
    {
        $admin_user = User::factory()->createOne();
        $admin_user->assignRole(Role::SUPER_ADMIN_ROLE->value);
        $this->actingAs($admin_user);
       
        $category = Category::factory()->createOne();
        
        $response = $this->json('POST', 'api/articles', [
            'name' => 'TEST ARTICLE',
            'stock' => 2,
            'category_id' => $category->id,
            'price_unit' => 10.22,
        ]);
        
        $response->assertOk();
        
        $this->assertDatabaseHas('articles', [
            'name' => 'TEST ARTICLE',
            'stock' => 2,
            'category_id' => $category->id,
            'price_unit' => 10.22,
        ]);

        //create the same article with the same category
        $response = $this->json('POST', 'api/articles', [
            'name' => 'TEST ARTICLE',
            'category_id' => $category->id,
        ]);
        
        $response->assertStatus(422);
    }

    /**
     * Test operator can´t create an article
     * 
     * @return void
     */
    public function testOperatorCantStoreArticle(): void
    {
        $operator_user = User::factory()->createOne();
        $operator_user->assignRole(Role::OPERATION_ROLE->value);
        $this->actingAs($operator_user);
       
        $category = Category::factory()->createOne();
        
        $response = $this->json('POST', 'api/articles', [
            'name' => 'TEST ARTICLE',
            'stock' => 2,
            'category_id' => $category->id,
            'price_unit' => 10.22,
        ]);
        
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'User does not have the right roles.',
            'exception' => 'Spatie\\Permission\\Exceptions\\UnauthorizedException',
        ]);
    }

    /**
     * Paginated structure to index endpoint.
     * 
     * @return array
     */
    protected function getPaginatedStructure(): array
    {
        return [
            'status',
            'data' => [
                'data' => [
                    '*' => [ // Valida que cada artículo tenga esta estructura
                        'id',
                        'name',
                        'category' => [
                            'id',
                            'name',
                            'created_at',
                            'updated_at',
                        ],
                        'stock',
                        'price_unit',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ],
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ],
        ];
    }
}
