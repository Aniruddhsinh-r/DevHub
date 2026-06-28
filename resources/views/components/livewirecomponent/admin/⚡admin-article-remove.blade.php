<?php

use Livewire\Component;
use App\Models\Article;
use Livewire\Attributes\On;
use App\Actions\ArticleDelete;

new class extends Component
{
    #[On('trigger-delete')]
    // public function handleGlobalDelete($id, $type) {
    //     if ($type === 'adminArticleDelete') {
    //         $this->remove($id);
    //     }
    // }

    // public function remove($articleId) {
    //     $article = Article::findOrFail($articleId);
    //     $action = app(ArticleDelete::class);
    //     $action->handle($article);

    //     session()->flash('success', 'Article deleted successfully.');
    //     return $this->redirectRoute('admin.articles', navigate: true);
    // }
};
?>
