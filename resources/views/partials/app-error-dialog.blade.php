@php
    use App\Support\AppUserMessage;

    $resolvedDialog = $appDialog ?? session('app_dialog');
    if (! is_array($resolvedDialog) && session()->has('flash_error')) {
        $resolvedDialog = AppUserMessage::fromText((string) session('flash_error'), AppUserMessage::TYPE_ERROR);
    }
    if (! is_array($resolvedDialog) && session()->has('error')) {
        $resolvedDialog = AppUserMessage::fromText((string) session('error'), AppUserMessage::TYPE_ERROR);
    }
    if (! is_array($resolvedDialog) && session()->has('warning')) {
        $resolvedDialog = AppUserMessage::fromText((string) session('warning'), AppUserMessage::TYPE_WARNING);
    }
    if (! is_array($resolvedDialog) && session()->has('status')) {
        $resolvedDialog = AppUserMessage::fromText((string) session('status'), AppUserMessage::TYPE_INFO);
    }
    if (! is_array($resolvedDialog) && isset($errors) && $errors->any()) {
        $resolvedDialog = AppUserMessage::fromLines($errors->all(), AppUserMessage::TYPE_WARNING);
    }

    $dialogType = is_array($resolvedDialog) ? ($resolvedDialog['type'] ?? AppUserMessage::TYPE_ERROR) : AppUserMessage::TYPE_ERROR;
    $dialogTitle = is_array($resolvedDialog) ? ($resolvedDialog['title'] ?? AppUserMessage::titleFor($dialogType)) : AppUserMessage::titleFor(AppUserMessage::TYPE_ERROR);
    $dialogMessage = is_array($resolvedDialog) ? trim((string) ($resolvedDialog['message'] ?? '')) : '';
    $dialogVisible = ($visible ?? false) || $dialogMessage !== '';
@endphp
<div id="app-error-dialog"
     class="modal-backdrop app-error-dialog app-error-dialog--{{ $dialogType }} @if($dialogVisible) is-visible @endif"
     aria-hidden="{{ $dialogVisible ? 'false' : 'true' }}"
     role="alertdialog"
     aria-labelledby="app-error-dialog-title"
     aria-describedby="app-error-dialog-text"
     data-dialog-type="{{ $dialogType }}"
     @unless($dialogVisible) hidden @endunless>
    <div class="modal app-error-dialog-panel" role="document">
        <div class="app-error-dialog-header">
            <h2 id="app-error-dialog-title" data-app-error-title>{{ $dialogTitle }}</h2>
            <button type="button" class="modal-close" data-app-error-close aria-label="إغلاق">&times;</button>
        </div>
        <p id="app-error-dialog-text" class="app-error-dialog-text" data-app-error-text>{{ $dialogMessage }}</p>
        <div class="app-error-dialog-actions">
            <button type="button" class="btn-primary" data-app-error-close>حسناً</button>
        </div>
    </div>
</div>
