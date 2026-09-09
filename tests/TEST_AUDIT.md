# QA Report

| What I broke | Did a test catch it? | Which test | What I did |
|---|---|---|---|
| `//Gate::authorize('update', $article)` in `ArticleController@update` | Yes | `Api\ArticleapiTest > author cannot update another user article` | Nothing needed |
| `$data['user_id'] = 5;` change in `CreateArticle.php` | Yes | `Article\CreateArticleTest > user can create an article & other` | Nothing needed |
| `//$user->assignRole(UserRole::AUTHOR)` in `AuthController` | No | — | Added role check in `AuthapiTest` |
| `->default(ArticleStatus::PUBLISHED)` change in `ArticleForm` | No | — | Add test case in `CreateArticleTest` |
| `//Gate::authorize('bookmark', $article) — add bookmark` in `BookmarkController` | Yes | `Api\BookmarkapiTest > user cannot bookmark their own article & user without permission cannot bookmark an article` | Nothing needed |
| `//Gate::authorize('bookmark', $article) — remove bookmark` in `BookmarkController` | No | — | Add test case in `BookmarkapiTest` |
| `'name' => ['string', 'min:2', 'max:5', change rules — update category` in `CategoryController` | Yes | `Api\CategoryapiTest > admin can update their owned category & superadmin can update any category` | Nothing needed |
| `$comment->delete()` — comment deletion in `CommentController` | No | — | Add test case in `CommentapiTest` to verify the comment is soft deleted |

- all policy checked by comment out some function and plicy test case fail properly.
- check comment out some gates and function and add test that not recognise that code or feature.
- commentout token in invitation and check every single leaks $token = Str::random(32); but all passed.
- i breake some validation rules and status too but my test throw error on that.

### Additional observation

### Additional observation

* I did my best to test and cover the possible hidden bugs based on my understanding. However, no matter how thoroughly a system is tested, some unexpected bugs may still remain and can only be discovered in real-world production use.
