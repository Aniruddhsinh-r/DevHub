# API Abuse and Input Validation Findings

## Write Endpoint Findings

| Endpoint                                            | What abusing it would create/change      | Seriousness | Reasoning                                                                                       |
| --------------------------------------------------- | ---------------------------------------- | ----------- | ----------------------------------------------------------------------------------------------- |
| `POST /api/v1/article/create`                       | Creates new article records and content  | High        | Repeated requests can create a large number of database records and stored content.             |
| `PUT /api/v1/article/{slug}/update`                 | Updates an existing article              | Low         | It only changes the author's existing article and does not continuously create new rows.        |
| `DELETE /api/v1/article/{slug}/delete`              | Deletes an author's article              | Low         | The author is managing their own article, so repeated deletion does not create database growth. |
| `POST /api/v1/article/{slug}/comment`               | Creates comment records                  | High        | Repeated requests can create many database rows.                                                |
| `POST /api/v1/article/{slug}/comment/reply`         | Creates reply records                    | High        | Repeated requests can create many database rows, so comments and replies need a shared limit.   |
| `POST /api/v1/article/{slug}/like`                  | Creates a like relationship              | Low         | Existing application/database logic prevents unlimited duplicate likes.                         |
| `POST /api/v1/article/{slug}/bookmark`              | Creates a bookmark relationship          | Low         | Existing application/database logic prevents unlimited duplicate bookmarks.                     |
| `POST /api/v1/user/{uuid}/follow`                   | Creates a follow relationship            | Low         | Existing application/database logic prevents unlimited duplicate follow relationships.          |
| `POST /api/v1/admin/category/create`                | Creates category records                 | Medium      | Repeated requests can create unnecessary category records.                                      |
| `PUT /api/v1/admin/category/{category}/update`      | Updates category data                    | Medium      | Only superadmins can update all categories, so it is left unlimited as it is considered safe.    |
| `DELETE /api/v1/admin/category/{category}/delete`   | Deletes categories                       | Critical    | It is an administrative destructive action and is therefore limited.                            |
| `POST /api/v1/admin/article/create`                 | Creates article records and content      | High        | Repeated requests can create many persistent records and stored content.                        |
| `DELETE /api/v1/admin/article/{slug}/delete`        | Soft-deletes articles                    | High        | Admins can delete other users' content, so repeated use can have a significant impact.          |
| `DELETE /api/v1/admin/article/{slug}/forcedelete`   | Permanently deletes articles             | High        | Permanent deletion is destructive and can permanently remove data.                              |
| `DELETE /api/v1/admin/users/{uuid}/delete`          | Soft-deletes users                       | Critical        | Deleting users can also affect their articles and other user-related data, so deletion needs protection.                 |
| `DELETE /api/v1/admin/users/{uuid}/forcedelete`     | Permanently deletes users                | High        | Permanent user deletion is a destructive administrative action.                                 |
| `POST /api/v1/admin/invitation/send`                | Creates an invitation and sends an email | Critical    | Repeated requests can create invitation records and send large numbers of emails.               |
| `POST /api/v1/admin/invitation/{invitation}/resend` | Sends another invitation email           | High        | Repeated resends can generate many outgoing emails.                                             |
| `PUT /api/v1/profile/update`                        | Updates the user's profile               | Low         | It changes existing data and does not continuously create new records.                          |
| `DELETE /api/v1/comment/{comment}/delete`           | Deletes an existing comment              | Low         | It manages existing content and does not create additional rows.                                |

## What I Fixed

| Area                                              |                  Limit | Reason                                                                                                   |
| ------------------------------------------------- | ---------------------: | -------------------------------------------------------------------------------------------------------- |
| Author article creation                           |          10 / 24 hours | Prevents an author from creating too many articles.                                                      |
| Comments + replies                                | 50 / 24 hours combined | Prevents excessive comment/reply creation while allowing normal use.                                     |
| Like + bookmark + follow                          | 100 / 24 hours         | Limits repeated relationship actions while still allowing normal user activity. |
| Admin category create/delete                      | 4 / 24 hours      |  Reassigned category management because creating and deleting categories can cause significant changes to article-related data.  |
| Admin user delete/force-delete |          10 / 24 hours | Limits heavy administrative changes and persistent data creation/deletion actions.                               |
| Admin article creation | 5 / 24 hours | Admin article publishing is limited because it is not expected to be a high-priority administrative activity. |
| Admin article delete + force-delete                 | 50 / 24 hours         | Admins can delete articles in bulk, so the limit reduces the impact of excessive destructive actions.                       |
| Invitation send + resend                          | 10 / 24 hours combined | Limits the total number of invitation emails an admin can trigger, including both new sends and resends. |
| Pagination | 1–100 records per page | Improved `per_page` validation. It now accepts only integer values from 1 to 100. |
- Added test cases for each rate limit to verify that the request after the allowed limit returns 429 Too Many Requests.
- Added pagination tests to verify valid page sizes and reject invalid values such as values above 100, negative numbers, zero, and non-numeric input.

## What I Deliberately Left Alone

* **Author update/delete:** left unlimited because the author is managing their own content; admin deletion is limited because admins can perform destructive actions on other users' data, including bulk deletion.
* **Profile update:** left unlimited because it only updates existing user data.
* **Comment delete:** left unlimited because it removes existing content.
* **Dislike/remove/unfollow:** left unlimited because users can only remove their own existing relationships, and the application logic prevents removing something that was not previously added.

## Large Input Findings

### Pagination

The original code was:

```php
->paginate($request->get('per_page', 12))
```

Testing with `per_page=10000` in Postman returned **200 OK**. The API accepted the large page-size value.

The code was fixed to:

```php
$request->validate([
    'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
]);

->paginate($request->get('per_page', 12));
```

This limits the maximum page size to 100 records and rejects invalid values such as 0, negative numbers, and non-numeric input.

### Avatar Upload

The validation is:

```php
'avatar' => ['nullable', 'image', 'max:5120']
```

I created a text file, renamed it to `text.png`, and uploaded it as an avatar/article image. The upload was rejected with a **validation error**.

This showed that the `image` validation does not simply trust the `.png` filename/extension.
