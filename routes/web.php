<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ProjectAttachmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PortfolioCategoryController;
use Illuminate\Support\Facades\Route;

// Rotas de Visitantes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Rotas Protegidas (Requer Login)
Route::middleware('auth')->group(function () {
    // API de Status do Kanban
    Route::patch('/api/projects/{project}/status', [ProjectController::class, 'updateStatus'])
        ->name('projects.update-status');

    // Kanban Dynamic Columns and Move API routes
    Route::post('/api/kanban/columns', [DashboardController::class, 'storeColumn'])->name('kanban.columns.store');
    Route::put('/api/kanban/columns/{column}', [DashboardController::class, 'updateColumn'])->name('kanban.columns.update');
    Route::delete('/api/kanban/columns/{column}', [DashboardController::class, 'deleteColumn'])->name('kanban.columns.destroy');
    Route::patch('/api/projects/{project}/kanban-move', [DashboardController::class, 'moveProject'])->name('projects.kanban-move');
    Route::post('/api/kanban/columns/reorder', [DashboardController::class, 'moveColumnPosition'])->name('kanban.columns.reorder');
});

// Rota de Usuário Aguardando Aprovação
Route::middleware('auth')->get('/freelas/waiting-approval', function () {
    return view('auth.waiting-approval');
})->name('waiting-approval');

// Rotas Web Protegidas sob prefixo /freelas
Route::middleware(['auth', 'approved'])->prefix('freelas')->group(function () {
    // Dashboard principal
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Projetos / Orçamentos do usuário logado (Tenancy)
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/import-json', [ProjectController::class, 'importJson'])->name('projects.import-json');
    Route::post('/projects/analyze-similarity', [ProjectController::class, 'analyzeSimilarity'])->name('projects.analyze-similarity');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Anexos / Documentos dos Projetos
    Route::post('/projects/{project}/proposal/custom-link', [ProjectController::class, 'updateProposalCustomLink'])->name('projects.proposal.custom-link');
    Route::post('/projects/{project}/attachments', [ProjectAttachmentController::class, 'store'])->name('projects.attachments.store');
    Route::delete('/attachments/{attachment}', [ProjectAttachmentController::class, 'destroy'])->name('projects.attachments.destroy');
    Route::get('/attachments/{attachment}/download', [ProjectAttachmentController::class, 'download'])->name('projects.attachments.download');
    Route::patch('/attachments/{attachment}/classification', [ProjectAttachmentController::class, 'updateClassification'])->name('projects.attachments.classification');

    // Pagamentos dos Projetos
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/payments/{payment}/download-invoice', [PaymentController::class, 'downloadInvoice'])->name('payments.download-invoice');

    // Contas Bancárias e Cartões de Crédito (Carteira)
    Route::resource('bank-accounts', \App\Http\Controllers\BankAccountController::class);
    Route::put('/bank-accounts/{bank_account}/balance', [\App\Http\Controllers\BankAccountController::class, 'updateBalance'])->name('bank-accounts.update-balance');
    Route::resource('credit-cards', \App\Http\Controllers\CreditCardController::class)->except(['index']);

    // Controle Financeiro
    Route::get('/finances/mei', [\App\Http\Controllers\MeiController::class, 'index'])->name('finances.mei');
    Route::get('/finances/mei/export-csv', [\App\Http\Controllers\MeiController::class, 'exportCsv'])->name('finances.mei.export-csv');
    Route::post('/finances/mei/limit', [\App\Http\Controllers\MeiController::class, 'updateLimit'])->name('finances.mei.limit');
    Route::post('/finances/mei/upload-invoice', [\App\Http\Controllers\MeiController::class, 'uploadInvoice'])->name('finances.mei.upload-invoice');
    Route::post('/finances/mei/delete-invoice', [\App\Http\Controllers\MeiController::class, 'deleteInvoice'])->name('finances.mei.delete-invoice');
    Route::post('/finances/mei/replace-invoice', [\App\Http\Controllers\MeiController::class, 'replaceInvoice'])->name('finances.mei.replace-invoice');
    
    Route::resource('finances/categories', \App\Http\Controllers\CategoryController::class)->except(['show'])->names([
        'index' => 'finances.categories.index',
        'create' => 'finances.categories.create',
        'store' => 'finances.categories.store',
        'edit' => 'finances.categories.edit',
        'update' => 'finances.categories.update',
        'destroy' => 'finances.categories.destroy',
    ]);

    Route::resource('finances', \App\Http\Controllers\FinanceController::class)->except(['show']);
    Route::post('/finances/batch-destroy', [\App\Http\Controllers\FinanceController::class, 'batchDestroy'])->name('finances.batch-destroy');
    Route::post('/finances/{transaction}/duplicate', [\App\Http\Controllers\FinanceController::class, 'duplicate'])->name('finances.duplicate');
    Route::post('/finances/{transaction}/toggle-status', [\App\Http\Controllers\FinanceController::class, 'toggleStatus'])->name('finances.toggle-status');
    Route::get('/finances/{transaction}/download-attachment', [\App\Http\Controllers\FinanceController::class, 'downloadAttachment'])->name('finances.download-attachment');
    Route::get('/finances/{transaction}/preview-attachment', [\App\Http\Controllers\FinanceController::class, 'previewAttachment'])->name('finances.preview-attachment');
    Route::post('/finances/credit-card/{creditCard}/pay-invoice', [\App\Http\Controllers\FinanceController::class, 'payInvoice'])->name('finances.pay-invoice');
    Route::post('/finances/credit-card/{creditCard}/unpay-invoice', [\App\Http\Controllers\FinanceController::class, 'unpayInvoice'])->name('finances.unpay-invoice');
    Route::post('/finances/import-json', [\App\Http\Controllers\FinanceController::class, 'importJson'])->name('finances.import-json');
    Route::post('/finances/transfer', [\App\Http\Controllers\FinanceController::class, 'transfer'])->name('finances.transfer');
        
    // Perfil do Usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Rotas Privadas / Recursos (Apenas Master)
    Route::middleware('master')->group(function () {
        // Clientes do usuário logado (Tenancy) - Ordem correta de rotas
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/clients/merge', [ClientController::class, 'merge'])->name('clients.merge');
            
        // Autores do usuário logado (Tenancy)
        Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
        Route::get('/authors/create', [AuthorController::class, 'create'])->name('authors.create');
        Route::post('/authors', [AuthorController::class, 'store'])->name('authors.store');
        Route::get('/authors/{author}', [AuthorController::class, 'show'])->name('authors.show');
        Route::get('/authors/{author}/edit', [AuthorController::class, 'edit'])->name('authors.edit');
        Route::put('/authors/{author}', [AuthorController::class, 'update'])->name('authors.update');
        Route::delete('/authors/{author}', [AuthorController::class, 'destroy'])->name('authors.destroy');
        Route::post('/authors/merge', [AuthorController::class, 'merge'])->name('authors.merge');
            
        // Portfólio do usuário logado (Tenancy)
        Route::get('/portfolio/pipeline', [PortfolioController::class, 'pipeline'])->name('portfolio.pipeline');
        Route::get('/portfolio-settings', [PortfolioController::class, 'settings'])->name('portfolio.settings');
        Route::put('/portfolio-settings', [PortfolioController::class, 'updateSettings'])->name('portfolio.settings.update');
        Route::resource('/portfolio', PortfolioController::class);
        Route::resource('/portfolio-categories', PortfolioCategoryController::class)->except(['show', 'create', 'edit']);

        // Controle de Usuários e Configurações
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Configurações Administrativas do Sistema
        Route::get('/admin/settings', [\App\Http\Controllers\AdminSystemController::class, 'index'])->name('admin.settings.index');
        Route::put('/admin/settings', [\App\Http\Controllers\AdminSystemController::class, 'updateSettings'])->name('admin.settings.update');
        Route::post('/admin/users/{user}/approve', [\App\Http\Controllers\AdminSystemController::class, 'approveUser'])->name('admin.users.approve');
        Route::post('/admin/users/{user}/disapprove', [\App\Http\Controllers\AdminSystemController::class, 'disapproveUser'])->name('admin.users.disapprove');
        Route::patch('/admin/users/{user}/role', [\App\Http\Controllers\AdminSystemController::class, 'changeRole'])->name('admin.users.role');

        // Utilidades - Revisão de Trabalhos
        Route::get('/utilidades/revisoes', [\App\Http\Controllers\ProjectRevisionController::class, 'index'])->name('revisoes.index');
        Route::post('/utilidades/revisoes', [\App\Http\Controllers\ProjectRevisionController::class, 'store'])->name('revisoes.store');
        Route::get('/utilidades/revisoes/{revision}', [\App\Http\Controllers\ProjectRevisionController::class, 'show'])->name('revisoes.show');
        Route::delete('/utilidades/revisoes/{revision}', [\App\Http\Controllers\ProjectRevisionController::class, 'destroy'])->name('revisoes.destroy');
        
        // Autocomplete / AJAX Helpers
        Route::get('/utilidades/api/autores', [\App\Http\Controllers\ProjectRevisionController::class, 'searchAuthors'])->name('revisoes.api.authors');
        Route::get('/utilidades/api/projetos-autor/{author}', [\App\Http\Controllers\ProjectRevisionController::class, 'getProjectsByAuthor'])->name('revisoes.api.projects');

        // Rodadas de Ajustes e Gerenciamento de Arquivos
        Route::post('/utilidades/revisoes/{revision}/rounds', [\App\Http\Controllers\RevisionRoundController::class, 'storeRound'])->name('revisoes.rounds.store');
        Route::delete('/utilidades/rounds/{round}', [\App\Http\Controllers\RevisionRoundController::class, 'destroyRound'])->name('revisoes.rounds.destroy');
        Route::patch('/utilidades/rounds/{round}/status', [\App\Http\Controllers\RevisionRoundController::class, 'updateRoundStatus'])->name('revisoes.rounds.status');

        Route::get('/utilidades/rounds/{round}/files', [\App\Http\Controllers\RevisionRoundController::class, 'manageFiles'])->name('revisoes.rounds.files');
        Route::post('/utilidades/rounds/{round}/files', [\App\Http\Controllers\RevisionRoundController::class, 'uploadFiles'])->name('revisoes.rounds.upload');
        Route::delete('/utilidades/files/{file}', [\App\Http\Controllers\RevisionRoundController::class, 'deleteFile'])->name('revisoes.files.destroy');

        // Utilidades - Compartilhamento de Arquivos (estilo WeTransfer)
        Route::get('/utilidades/compartilhamento', [\App\Http\Controllers\FileShareController::class, 'index'])->name('revisoes.shares.index');
        Route::get('/utilidades/compartilhamento/novo', [\App\Http\Controllers\FileShareController::class, 'create'])->name('revisoes.shares.create');
        Route::post('/utilidades/compartilhamento', [\App\Http\Controllers\FileShareController::class, 'store'])->name('revisoes.shares.store');
        Route::delete('/utilidades/compartilhamento/{share}', [\App\Http\Controllers\FileShareController::class, 'destroy'])->name('revisoes.shares.destroy');
        Route::post('/utilidades/compartilhamento/{share}/toggle-active', [\App\Http\Controllers\FileShareController::class, 'toggleActive'])->name('revisoes.shares.toggle-active');
        Route::put('/utilidades/compartilhamento/{share}/settings', [\App\Http\Controllers\FileShareController::class, 'updateSettings'])->name('revisoes.shares.settings');

        // Utilidades - Identidades Visuais
        Route::get('/utilidades/identidades-visuais', [\App\Http\Controllers\BrandGuidelineController::class, 'index'])->name('revisoes.brand-guidelines.index');
        Route::get('/utilidades/identidades-visuais/novo', [\App\Http\Controllers\BrandGuidelineController::class, 'create'])->name('revisoes.brand-guidelines.create');
        Route::post('/utilidades/identidades-visuais', [\App\Http\Controllers\BrandGuidelineController::class, 'store'])->name('revisoes.brand-guidelines.store');
        Route::get('/utilidades/identidades-visuais/{brandGuideline}/editar', [\App\Http\Controllers\BrandGuidelineController::class, 'edit'])->name('revisoes.brand-guidelines.edit');
        Route::put('/utilidades/identidades-visuais/{brandGuideline}', [\App\Http\Controllers\BrandGuidelineController::class, 'update'])->name('revisoes.brand-guidelines.update');
        Route::delete('/utilidades/identidades-visuais/{brandGuideline}', [\App\Http\Controllers\BrandGuidelineController::class, 'destroy'])->name('revisoes.brand-guidelines.destroy');
        Route::post('/utilidades/identidades-visuais/{brandGuideline}/toggle-active', [\App\Http\Controllers\BrandGuidelineController::class, 'toggleActive'])->name('revisoes.brand-guidelines.toggle-active');
        Route::get('/utilidades/identidades-visuais/{brandGuideline}/zip', [\App\Http\Controllers\BrandGuidelineController::class, 'downloadZip'])->name('revisoes.brand-guidelines.zip');

        // Utilidades - Banco de Assets
        Route::get('/utilidades/assets', [\App\Http\Controllers\AssetController::class, 'index'])->name('revisoes.assets.index');
        Route::post('/utilidades/assets', [\App\Http\Controllers\AssetController::class, 'store'])->name('revisoes.assets.store');
        Route::put('/utilidades/assets/{asset}', [\App\Http\Controllers\AssetController::class, 'update'])->name('revisoes.assets.update');
        Route::delete('/utilidades/assets/{asset}', [\App\Http\Controllers\AssetController::class, 'destroy'])->name('revisoes.assets.destroy');
        Route::get('/utilidades/assets/{asset}/download', [\App\Http\Controllers\AssetController::class, 'download'])->name('revisoes.assets.download');
        Route::post('/utilidades/assets/download-batch', [\App\Http\Controllers\AssetController::class, 'downloadBatch'])->name('revisoes.assets.download-batch');
        Route::post('/utilidades/assets/destroy-batch', [\App\Http\Controllers\AssetController::class, 'destroyBatch'])->name('revisoes.assets.destroy-batch');

        // Utilidades - Lembretes e Notas (Google Keep Style)
        Route::get('/utilidades/lembretes', [\App\Http\Controllers\ReminderController::class, 'index'])->name('lembretes.index');
        Route::post('/utilidades/lembretes', [\App\Http\Controllers\ReminderController::class, 'store'])->name('lembretes.store');
        Route::put('/utilidades/lembretes/{reminder}', [\App\Http\Controllers\ReminderController::class, 'update'])->name('lembretes.update');
        Route::delete('/utilidades/lembretes/{reminder}', [\App\Http\Controllers\ReminderController::class, 'destroy'])->name('lembretes.destroy');
        Route::post('/utilidades/lembretes/{reminder}/pin', [\App\Http\Controllers\ReminderController::class, 'togglePin'])->name('lembretes.pin');
        Route::post('/utilidades/lembretes/{reminder}/color', [\App\Http\Controllers\ReminderController::class, 'updateColor'])->name('lembretes.color');
        Route::post('/utilidades/lembretes/{reminder}/archive', [\App\Http\Controllers\ReminderController::class, 'toggleArchive'])->name('lembretes.archive');
        Route::post('/utilidades/lembretes/reorder', [\App\Http\Controllers\ReminderController::class, 'reorder'])->name('lembretes.reorder');

        // Utilidades - Notificações Globais
        Route::get('/utilidades/notifications', [\App\Http\Controllers\ReminderController::class, 'getGlobalNotifications'])->name('lembretes.notifications');
        Route::post('/utilidades/notifications/{id}/read', [\App\Http\Controllers\ReminderController::class, 'markNotificationAsRead'])->name('lembretes.notifications.read');

        // Histórico de Notificações
        Route::get('/utilidades/notificacoes', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/utilidades/notificacoes/lidas', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/utilidades/notificacoes/limpar', [\App\Http\Controllers\NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        Route::delete('/utilidades/notificacoes/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    });
});

// Rotas públicas de orçamentos (propostas) para aprovação e rejeição pelo cliente final
Route::prefix('proposal/{hash}')->name('proposal.')->group(function () {
    Route::get('/', [ProposalController::class, 'show'])->name('show');
    Route::post('/approve', [ProposalController::class, 'approve'])->name('approve');
    Route::post('/reject', [ProposalController::class, 'reject'])->name('reject');
});

// Rotas públicas retrocompatíveis com o formato antigo de orçamentos (/orcamento/hash)
Route::prefix('orcamento/{hash}')->name('orcamento.')->group(function () {
    Route::get('/', [ProposalController::class, 'show'])->name('show_legacy');
    Route::post('/approve', [ProposalController::class, 'approve'])->name('approve_legacy');
    Route::post('/reject', [ProposalController::class, 'reject'])->name('reject_legacy');
});

// Rota pública de extrato para o cliente final
Route::get('/shared/client/{share_token}/statement', [ClientController::class, 'publicStatement'])->name('public.client.statement');

// Rotas do Portfólio Público (Danilo Miguel)
Route::middleware('maintenance')->group(function () {
    Route::get('/', [\App\Http\Controllers\PublicPortfolioController::class, 'index'])->name('public.home');
    Route::get('/trabalho/{slug}', [\App\Http\Controllers\PublicPortfolioController::class, 'show'])->name('public.portfolio.show');
    Route::post('/portfolio/{id}/views', [\App\Http\Controllers\PublicPortfolioController::class, 'incrementViews'])->name('public.portfolio.views');
    Route::post('/portfolio/{id}/likes', [\App\Http\Controllers\PublicPortfolioController::class, 'incrementLikes'])->name('public.portfolio.likes');
    Route::post('/contato', [\App\Http\Controllers\PublicPortfolioController::class, 'sendContact'])->name('public.contact.send');
});

// Rotas Públicas de Compartilhamento de Arquivos
Route::get('/compartilhar/{share_token}', [\App\Http\Controllers\FileShareController::class, 'publicShow'])->name('public.share.show');
Route::post('/compartilhar/{share_token}/verify', [\App\Http\Controllers\FileShareController::class, 'publicVerifyPassword'])->name('public.share.verify');
Route::get('/compartilhar/{share_token}/download/{itemId}', [\App\Http\Controllers\FileShareController::class, 'publicDownloadFile'])->name('public.share.download');
Route::get('/compartilhar/{share_token}/zip', [\App\Http\Controllers\FileShareController::class, 'publicDownloadZip'])->name('public.share.zip');

// Rotas Públicas de Revisão de Trabalhos (Cliente)
Route::get('/revisao/{token}', [\App\Http\Controllers\PublicRevisionController::class, 'show'])->name('public.revisao.show');
Route::post('/revisao/file/{file}/annotations', [\App\Http\Controllers\PublicRevisionController::class, 'storeAnnotation'])->name('public.revisao.annotation.store');
Route::delete('/revisao/annotation/{annotation}', [\App\Http\Controllers\PublicRevisionController::class, 'deleteAnnotation'])->name('public.revisao.annotation.destroy');
Route::post('/revisao/annotation/{annotation}/resolve', [\App\Http\Controllers\PublicRevisionController::class, 'resolveAnnotation'])->name('public.revisao.annotation.resolve');
Route::post('/revisao/annotation/{annotation}/update', [\App\Http\Controllers\PublicRevisionController::class, 'updateAnnotation'])->name('public.revisao.annotation.update');
Route::get('/revisao/file/{file}/download', [\App\Http\Controllers\PublicRevisionController::class, 'downloadFile'])->name('public.revisao.download.file');
Route::get('/revisao/round/{round}/download-all', [\App\Http\Controllers\PublicRevisionController::class, 'downloadAllFiles'])->name('public.revisao.download.all');
Route::get('/revisao/round/{round}/download-annotations', [\App\Http\Controllers\PublicRevisionController::class, 'downloadAnnotationsReport'])->name('public.revisao.download.report');

// Rota pública de Identidade Visual
Route::get('/brand/{token}', [\App\Http\Controllers\PublicBrandController::class, 'show'])->name('public.brand.show');


