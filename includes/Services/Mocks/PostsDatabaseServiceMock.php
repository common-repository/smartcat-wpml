<?php

namespace Smartcat\Includes\Services\Mocks;

use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;

class PostsDatabaseServiceMock implements PostsDatabaseInterface
{
    public function getPosts(array $args = []): array
    {
        $post1 = new \stdClass();
        $post1->ID = -99;
        $post1->post_author = 1;
        $post1->post_date = '2022-01-01 01:01:01';
        $post1->post_date_gmt = '2022-01-01 01:01:01';
        $post1->post_title = 'Some title or other';
        $post1->post_content = 'Whatever you want here. Maybe some cat pictures....';
        $post1->post_status = 'publish';
        $post1->comment_status = 'closed';
        $post1->ping_status = 'closed';
        $post1->post_name = 'fake-page-' . 123123;
        $post1->post_type = 'page';
        $post1->filter = 'raw';

        $post2 = new \stdClass();
        $post2->ID = -99;
        $post2->post_author = 1;
        $post2->post_date = '2022-01-01 01:01:01';
        $post2->post_date_gmt = '2022-01-01 01:01:01';
        $post2->post_title = 'Another fake post';
        $post2->post_content = 'And here is another fake post description';
        $post2->post_status = 'publish';
        $post2->comment_status = 'closed';
        $post2->ping_status = 'closed';
        $post2->post_name = 'fake-page-' . 234234;
        $post2->post_type = 'post';
        $post2->filter = 'raw';

        return [$post1, $post2];
    }

    public function normalizePostName(int $postId, string $originalPostName): string
    {
        return "mock-$originalPostName-$postId";
    }

    public function getCategoriesIds(int $postId): array
    {
        return [1, 2, 3];
    }

    public function insertPost(array $postData): int
    {
        return 0;
    }

    public function updatePostContent(int $postId, $content)
    {
        // TODO: Implement updatePostContent() method.
    }
}