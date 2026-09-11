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

- Checked all policies by commenting out some functions; the related policy test cases failed as expected. Added test cases for the two UserPolicy methods.
- Commented out some gates and functions and added tests for code or features that were not previously covered.
- Commented out the token generation in the invitation flow and checked for any related leaks; all tests still passed.
- I broke some validation rules and the article status logic, and my tests caught those changes.
- Created a BookmarkFactory, then checked the bookmark remove method and added a test case for it.
- During the audit, I deleted two policy methods I thought were unused. Nothing failed, which is how I learned no test covered them. But they control the bulk restore and force delete buttons on the admin Users page, so deleting them broke both buttons for every user, including the superadmin. I restored them and wrote the tests.
---

## Findings and Changes

# QA Coverage Report

## Part 1 — Empty Spaces

| File / Method | Coverage | Decision | Why |
|---|---:|---|---|
| `DashboardStats.php` | 0% | Delete | The widget was not registered or used anywhere. It also had an incorrect Total Likes implementation using `Bookmark::count()`. |
| `CreateArticle` page | 0% | Needs a test | Added a browser test to execute the Admin Create Article page. |
| `MyArticles` page | 0% | Needs a test | Added a browser test for the author's My Articles page. |
| `Profile` page | 0% | Needs a test | Added a browser test for the author's Profile page. |
| `StatsOverview` widget | 0% | Needs a test | Added a test that visits the Admin dashboard and executes the widget. |
| `LatestArticles` widget | 0% | Needs a test | Covered by visiting the Admin dashboard. |
| `TopAuthors` widget | 0% | Needs a test | Covered by visiting the Admin dashboard. |
| `LikesRelationManager` | 0% | Needs a test | Added a browser test in `tests/Feature/BrowserTest/Admin/AdminArticleTest.php`. |
| `FollowersRelationManager` | 0% | Needs a test | Added browser coverage for the Followers tab. |
| `FollowingRelationManager` | 0% | Needs a test | Added browser coverage for the Following tab. |
| `Bookmark`, `Follow`, `Like`, `View` models | 0% | Fine to leave | Their functionality is already exercised through existing API/feature tests, even though the model files themselves show 0%. |

## Part 2 — Tests Added

- Admin Create Article → `tests/Feature/BrowserTest/Admin/AdminArticleTest.php`
- My Articles → `tests/Feature/BrowserTest/Articles/ArticleTest.php`
- Author Profile → `tests/Feature/BrowserTest/users/ProfileUpdateTest.php`
- Admin dashboard widgets → `tests/Feature/BrowserTest/Admin/AdminArticleTest.php`
- Likes Relation Manager → `tests/Feature/BrowserTest/Admin/AdminArticleTest.php`
- Followers/Following Relation Managers → `tests/Feature/BrowserTest/users/FollowTest.php`

## Part 3 — Coverage Setup

Installed PCOV for PHP coverage:

```bash
pecl install pcov

### Additional observation

- I did my best to test and cover the possible hidden bugs based on my understanding. However, no matter how thoroughly a system is tested, some unexpected bugs may still remain and can only be discovered in real-world production use.
