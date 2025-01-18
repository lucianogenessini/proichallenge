<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\Article\IndexArticleRequest;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\Article\ArticleResource;
use App\Models\Article;
use App\Models\BaseFilter;
use Illuminate\Http\Response;

class ArticleController extends ApiController
{
    /**
     * Returns a list of articles
     *
     * @param IndexArticleRequest $request
     *
     * @return Response
     */
    public function index(IndexArticleRequest $request): Response
    {
        $articles = Article::filter($request->only(['search', 'categories']));

        $filters = BaseFilter::fromRequest($request, $articles);
        $articles = Article::listFromParams($filters);

        return $this->successResponse(ArticleResource::collection($articles)->response()->getData(true));
    }

    /**
     * Store an article
     *
     * @param StoreArticleRequest $request
     *
     * @return Response
     */
    public function store(StoreArticleRequest $request): Response
    {
        $article = Article::create($request->validated());
        return $this->successResponse(new ArticleResource($article));
    }

    /**
     * Update an article
     *
     * @param Article $article
     *
     * @return Response
     */
    public function update(UpdateArticleRequest $request, Article $article): Response
    {
        $article->update($request->validated());
        $article->resolveStockQuantity();
        return $this->successResponse(new ArticleResource($article));
    }

    /**
     * Destroy an article
     *
     * @param Article $article
     *
     * @return Response
     */
    public function destroy(Article $article): Response
    {
        $article->delete();
        return $this->successResponse(new ArticleResource($article));
    }
}
