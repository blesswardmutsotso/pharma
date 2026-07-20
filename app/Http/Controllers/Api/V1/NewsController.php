<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $posts = NewsPost::published()
            ->select('id', 'title', 'body', 'category', 'published_at')
            ->paginate($request->get('per_page', 20));

        return $this->success(
            $posts->items(),
            'News retrieved.',
            200,
            [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'total'        => $posts->total(),
            ]
        );
    }

    public function show($id)
    {
        $post = NewsPost::published()->find($id);

        if (!$post) {
            return $this->error('Post not found.', 404);
        }

        return $this->success($post);
    }
}
