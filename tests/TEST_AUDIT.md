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

## Findings and Change

## QA Coverage Report

### Empty Spaces

| File / Method | Coverage | Decision | Why |
|---|---:|---|---|
| `DashboardStats.php` | 0% | Delete | The widget was not registered or used anywhere. It also had an incorrect Total Likes implementation using `Bookmark::count()`. |
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
```

## API Endpoints

- Re-reviewed the previous API endpoints against the existing API test cases.
- Added API tests for `ArticleApiTest`, `AuthApiTest`, and `UserApiTest`, including authentication handling.
- Added the missing User API test to verify an issue that had already been fixed.

## — Short Answers

### 1. What surprised you most in the coverage report?

- After enabling coverage, I found that some files were still not tested directly. I also found one unused/dead file and a bug in the homepage display.

### 2. Name one thing that shows high coverage but is not really protected.

- Admin and user both have FollowTest.php/ProfileUpdateTest.php but no test ever clicks into the Followers/Following tab.

- The similar **Author CreateArticle** page was covered by `ArticleCreateTest.php`, but the Admin CreateArticle page uses a different route and had no test visiting it.
---

## assertions that match too much

- Added a new test to catch the dashboard bug and removed the old test.
- Run browser test cases more than 20 times after clearing optimize, routes, cache, views, and config. nothing failed, but `Admin fetch Article details` failes once after each clear, then passed on every subsequent run. I investigated and tried multiple changes but could not fix it, so it may be related to a cold-start issue.
- Added `Model::preventLazyLoading()` for testing and confirmed all tests pass.
- Checked Article and Comment validation in Laravel and API. Found two bug Comment max in laravel **500** and in api **1000** and Article set excerpt min to **20** in api which is missing.
- **Test folder:** Fixed the duplicate `Admin`/`admin` folder issue. Now only `tests/Feature/Admin/` is used.
---

### Additional observation

- I did my best to test and cover the possible hidden bugs based on my understanding. However, no matter how thoroughly a system is tested, some unexpected bugs may still remain and can only be discovered in real-world production use.
