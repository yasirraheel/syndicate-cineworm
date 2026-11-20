<?php

namespace App\Http\Controllers;

use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminPanelController extends Controller
{
    /** @var array<string, string> */
    private const PAGES = [
        'dashboard' => 'en/dashboard.html',
        'shared-subscriptions' => 'en/my-shared-subscriptions.html',
        'joined-subscriptions' => 'en/joined-subscriptions.html',
        'waiting-list' => 'en/waiting-list.html',
        'wallet' => 'en/my-wallet.html',
        'cards' => 'en/my-cards.html',
        'account' => 'en/my-account.html',
        'settings' => 'en/my-settings.html',
        'chat' => 'en/my-chat/subscriptions.html',
    ];

    public function show(string $section = 'dashboard'): Response
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        abort_unless(isset(self::PAGES[$section]), 404);

        if ($section === 'account') {
            return app(AdminUsersController::class)->edit(Auth::user(), 'Administrator account', '/en/admin/account');
        }

        return $this->adminResponse(
            app(CapturedPageController::class)->show(self::PAGES[$section]),
            $section,
        );
    }

    public function identity(): Response
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        return redirect()->route('admin.kyc');
    }

    protected function adminResponse(Response $response, ?string $section = null): Response
    {
        $html = (string) $response->getContent();
        $html = $this->injectAdminLoadingState($html);
        if ($section === 'dashboard') {
            $html = $this->adminDashboardContent($html);
        }

        $html = preg_replace(
            '/(<h1\b[^>]*\bclass="dashboard-header-title"[^>]*>\s*)Hello,\s*/i',
            '$1Admin panel · ',
            $html,
            1,
        ) ?? $html;
        $html = preg_replace(
            '/(<div class="dashboard-header-actions-title">\s*)Dashboard(\s*<\/div>)/i',
            '$1Admin dashboard$2',
            $html,
            1,
        ) ?? $html;

        $links = [
            'href="/en/dashboard"' => 'href="/en/admin"',
            'href="/en/my-shared-subscriptions"' => 'href="/en/admin/shared-subscriptions"',
            'href="/en/joined-subscriptions"' => 'href="/en/admin/joined-subscriptions"',
            'href="/en/waiting-list"' => 'href="/en/admin/waiting-list"',
            'href="/en/my-wallet"' => 'href="/en/admin/wallet"',
            'href="/en/my-cards"' => 'href="/en/admin/cards"',
            'href="/en/my-account/identity"' => 'href="/en/admin/kyc"',
            'href="/en/my-account"' => 'href="/en/admin/account"',
            'href="/en/my-settings"' => 'href="/en/admin/settings"',
            'href="/en/my-chat/subscriptions"' => 'href="/en/admin/chat"',
        ];
        $html = str_replace(array_keys($links), array_values($links), $html);

        $usersNavigation = <<<'HTML'
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/users">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M15 5C16.3261 5 17.5979 5.52678 18.5355 6.46447C19.4732 7.40215 20 8.67392 20 10C20 11.3261 19.4732 12.5979 18.5355 13.5355C17.5979 14.4732 16.3261 15 15 15C13.6739 15 12.4021 14.4732 11.4645 13.5355C10.5268 12.5979 10 11.3261 10 10C10 8.67392 10.5268 7.40215 11.4645 6.46447C12.4021 5.52678 13.6739 5 15 5V5ZM15 17.5C20.525 17.5 25 19.7375 25 22.5V25H5V22.5C5 19.7375 9.475 17.5 15 17.5Z" />
            </svg>
        </span>
        <span class="navi_dash-text">Users</span>
    </a>
</li>
HTML;
        $supportNavigation = <<<'HTML'
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/support">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M15 5C16.3261 5 17.5979 5.52678 18.5355 6.46447C19.4732 7.40215 20 8.67392 20 10C20 11.3261 19.4732 12.5979 18.5355 13.5355C17.5979 14.4732 16.3261 15 15 15C13.6739 15 12.4021 14.4732 11.4645 13.5355C10.5268 12.5979 10 11.3261 10 10C10 8.67392 10.5268 7.40215 11.4645 6.46447C12.4021 5.52678 13.6739 5 15 5V5ZM15 17.5C20.525 17.5 25 19.7375 25 22.5V25H5V22.5C5 19.7375 9.475 17.5 15 17.5Z" />
            </svg>
        </span>
        <span class="navi_dash-text">Support</span>
    </a>
</li>
HTML;
        $moduleNavigation = <<<'HTML'
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/brands">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M4 4h12l10 10-12 12L4 16V4z" />
            </svg>
        </span>
        <span class="navi_dash-text">Brands</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/categories">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="4" y="4" width="9" height="9" rx="1" />
                <rect x="17" y="4" width="9" height="9" rx="1" />
                <rect x="4" y="17" width="9" height="9" rx="1" />
                <rect x="17" y="17" width="9" height="9" rx="1" />
            </svg>
        </span>
        <span class="navi_dash-text">Categories</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/content/settings">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="4" y="7" width="22" height="2" />
                <rect x="4" y="14" width="22" height="2" />
                <rect x="4" y="21" width="22" height="2" />
                <circle cx="10" cy="8" r="3" />
                <circle cx="20" cy="15" r="3" />
                <circle cx="12" cy="22" r="3" />
            </svg>
        </span>
        <span class="navi_dash-text">Site Settings</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/how-it-works">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="4" y="20" width="7" height="6" />
                <rect x="11" y="14" width="7" height="12" />
                <rect x="18" y="8" width="8" height="18" />
            </svg>
        </span>
        <span class="navi_dash-text">How It Works</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/faqs">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path fill-rule="evenodd" d="M6 4h18v22H6V4zm4 6v2h10v-2H10v-2zm0 5v2h10v-2H10v-2zm0 5v2h6v-2h-6v-2z" />
            </svg>
        </span>
        <span class="navi_dash-text">FAQs</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/testimonials">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M6 6h8v8c0 4-2 6-5 7l-1-2c1.5-.5 2.5-1.5 2.5-3H6V6zm12 0h8v8c0 4-2 6-5 7l-1-2c1.5-.5 2.5-1.5 2.5-3h-4.5V6z" />
            </svg>
        </span>
        <span class="navi_dash-text">Testimonials</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/press">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path fill-rule="evenodd" d="M4 5h22v20H4V5zm4 4v2h14V9H8zm0 5v2h14v-2H8zm0 5v2h8v-2H8z" />
            </svg>
        </span>
        <span class="navi_dash-text">Press</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/footer-groups">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="3" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="10" cy="15" r="6" />
                <circle cx="20" cy="15" r="6" />
            </svg>
        </span>
        <span class="navi_dash-text">Footer Links</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/features">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M15 4l3.2 6.9 7.3.8-5.5 5 1.5 7.3L15 20.4 8.5 24 10 16.7 4.5 11.7l7.3-.8L15 4z" />
            </svg>
        </span>
        <span class="navi_dash-text">Features</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/countries">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="15" cy="15" r="11" fill="none" stroke="currentColor" stroke-width="2" />
                <path d="M4 15h22M15 4c3 3.5 3 18.5 0 22-3-3.5-3-18.5 0-22z" fill="none" stroke="currentColor" stroke-width="2" />
            </svg>
        </span>
        <span class="navi_dash-text">Countries</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/currencies">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="3" y="8" width="24" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2" />
                <circle cx="15" cy="15" r="4" fill="none" stroke="currentColor" stroke-width="2" />
            </svg>
        </span>
        <span class="navi_dash-text">Currencies</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/templates">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M4 6h22v18H4V6zm2 2v4h18V8H6zm0 6v8h7v-8H6zm9 0v3h7v-3h-7zm0 5v3h7v-3h-7z" />
            </svg>
        </span>
        <span class="navi_dash-text">Templates</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/reassurance">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M15 4l3.2 6.9 7.3.8-5.5 5 1.5 7.3L15 20.4 8.5 24 10 16.7 4.5 11.7l7.3-.8L15 4z" />
            </svg>
        </span>
        <span class="navi_dash-text">Reassurance</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/brand-faqs">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path fill-rule="evenodd" d="M6 4h18v22H6V4zm4 6v2h10v-2H10v-2zm0 5v2h10v-2H10v-2zm0 5v2h6v-2h-6v-2z" />
            </svg>
        </span>
        <span class="navi_dash-text">Brand FAQs</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/pages">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M8 3h9l5 5v19H8V3zm9 2.4V9h3.6L17 5.4zM11 13h8v2h-8v-2zm0 5h8v2h-8v-2zm0 5h5v2h-5v-2z" />
            </svg>
        </span>
        <span class="navi_dash-text">Pages</span>
    </a>
</li>
<li class="navi_dash-item ">
    <a class="navi_dash-link" href="/en/admin/offers">
        <span class="navi_dash-icon">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M4 6h22v18H4V6zm2 2v4h18V8H6zm0 6v8h7v-8H6zm9 0v3h7v-3h-7zm0 5v3h7v-3h-7z" />
            </svg>
        </span>
        <span class="navi_dash-text">Offers</span>
    </a>
</li>
HTML;
        $html = preg_replace(
            '/(<li class="navi_dash-item ">\s*<a class="navi_dash-link  " href="\/en\/admin\/account">)/i',
            $usersNavigation.$supportNavigation.$moduleNavigation.'$1',
            $html,
            1,
        ) ?? $html;

        // The captured dashboard contains the regular user's sidebar. Replace
        // the complete navigation so every admin page has admin-only links.
        $html = $this->replaceAdminNavigation($html);

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    protected function injectAdminLoadingState(string $html): string
    {
        if (str_contains($html, 'id="admin-page-loader"')) {
            return $html;
        }

        $loader = <<<'HTML'
<div id="admin-page-loader" role="status" aria-live="polite">
    <div class="admin-page-loader-spinner" aria-hidden="true"></div>
    <span>Loading admin panel…</span>
</div>
<style>
    #admin-page-loader { align-items:center; background:#f4f6fb; color:#123d68; display:flex; flex-direction:column; font-size:16px; font-weight:600; gap:14px; inset:0; justify-content:center; position:fixed; transition:opacity .18s ease, visibility .18s ease; visibility:visible; z-index:99999; }
    #admin-page-loader.is-hidden { opacity:0; pointer-events:none; visibility:hidden; }
    .admin-page-loader-spinner { animation:admin-page-loader-spin .8s linear infinite; border:4px solid rgba(1,109,244,.18); border-radius:50%; border-top-color:#016df4; height:42px; width:42px; }
    @keyframes admin-page-loader-spin { to { transform:rotate(360deg); } }
</style>
<script>
    (function () {
        var loader = document.getElementById('admin-page-loader');
        if (!loader) return;
        var hideLoader = function () {
            loader.classList.add('is-hidden');
            window.setTimeout(function () { loader.remove(); }, 220);
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hideLoader, { once: true });
        } else {
            hideLoader();
        }
    }());
</script>
HTML;

        return preg_replace('/(<body\b[^>]*>)/i', '$1'.$loader, $html, 1) ?? $html;
    }

    protected function replaceAdminNavigation(string $html): string
    {
        $navigationStart = strpos($html, '<ul class="navi_dash">');
        $contentStart = $navigationStart === false
            ? false
            : strpos($html, '<div class="col-12 col-lg-8">', $navigationStart);

        if ($navigationStart === false || $contentStart === false) {
            return $html;
        }

        return substr($html, 0, $navigationStart)
            .$this->adminNavigation()
            .'</div>'
            .substr($html, $contentStart);
    }

    protected function adminNavigation(): string
    {
        $currentPath = '/'.trim(request()->path(), '/');
        $items = [
            ['/en/admin', 'Dashboard', '<path d="M21 13C21 18.5 16.5 23 11 23S1 18.5 1 13 5.5 3 11 3v10h10Z"/><path d="M23 11c0-5.5-4.5-10-10-10v10h10Z"/>'],
            ['/en/admin/users', 'Users', '<path d="M15 14a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm-9 9c0-4 3.6-6 9-6s9 2 9 6H6Z"/>'],
            ['/en/admin/kyc', 'KYC reviews', '<path d="M6 3h12l4 4v14H6V3Zm3 6h7M9 13h7M9 17h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'],
            ['/en/admin/support', 'Support', '<path d="M22 12c0 5-4.5 9-10 9-1 0-2-.1-3-.4L4 23l1.2-4.1A8.5 8.5 0 0 1 2 12c0-5 4.5-9 10-9s10 4 10 9Z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="8" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="16" cy="12" r="1"/>'],
            ['/en/admin/brands', 'Brands', '<path d="M4 5h10l9 9-9 9-10-10V5Zm5 5h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'],
            ['/en/admin/categories', 'Categories', '<path d="M4 4h8v8H4V4Zm14 0h8v8h-8V4ZM4 18h8v8H4v-8Zm14 0h8v8h-8v-8Z"/>'],
            ['/en/admin/offers', 'Offers', '<path d="M4 6h22v18H4V6Zm2 2v4h18V8H6Zm0 6v8h7v-8H6Zm9 0v3h7v-3h-7Zm0 5v3h7v-3h-7Z"/>'],
            ['/en/admin/templates', 'Templates', '<path d="M5 4h20v22H5V4Zm4 6h12M9 15h12M9 20h7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'],
            ['/en/admin/content/settings', 'Site settings', '<path d="M4 7h22M4 15h22M4 23h22" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="10" cy="7" r="3"/><circle cx="20" cy="15" r="3"/><circle cx="13" cy="23" r="3"/>'],
            ['/en/admin/pages', 'Pages', '<path d="M7 3h10l5 5v19H7V3Zm10 1v5h5M10 14h9M10 19h9M10 24h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>'],
            ['/en/admin/faqs', 'FAQs', '<path d="M15 25a10 10 0 1 0-8-4l-3 4 5-1a10 10 0 0 0 6 1Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 11a3 3 0 1 1 5 2c-2 1-2 3-2 3M15 20h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'],
            ['/en/admin/account', 'Admin account', '<path d="M15 14a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm-9 9c0-4 3.6-6 9-6s9 2 9 6H6Z" fill="none" stroke="currentColor" stroke-width="2"/>'],
        ];

        $html = '<ul class="navi_dash">';
        foreach ($items as [$href, $label, $icon]) {
            $active = $currentPath === $href || ($href !== '/en/admin' && str_starts_with($currentPath, $href.'/'));
            $html .= '<li class="navi_dash-item '.($active ? 'active' : '').'">'
                .'<a class="navi_dash-link'.($active ? ' active' : '').'" href="'.e($href).'">'
                .'<span class="navi_dash-icon"><svg width="24" height="24" viewBox="0 0 30 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'.$icon.'</svg></span>'
                .'<span class="navi_dash-text">'.e($label).'</span></a></li>';
        }

        return $html.'</ul>';
    }

    protected function adminDashboardContent(string $html): string
    {
        $shareStart = strpos($html, '<div class="share-section dashboard mb-4 p-4">');
        $revenuesStart = strpos($html, '<div class="dashboard-progress dashboard-revenues-expenses mb-4 p-4">');
        $paymentsStart = strpos($html, '<div class="dashboard-progress dashboard-payments mb-4 p-4">');

        if ($shareStart === false || $revenuesStart === false || $paymentsStart === false || $revenuesStart <= $shareStart || $paymentsStart <= $revenuesStart) {
            return $html;
        }

        $adminCards = <<<'HTML'
<div class="share-section dashboard mb-4 p-4 mt-4">
    <h3 class="share-section-title cta-dashboard">Admin <u>overview</u></h3>
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="share-card green">
                <svg class="icon" viewBox="0 0 160 120" role="img" aria-label="Users and access">
                    <circle cx="80" cy="42" r="22" fill="none" stroke="currentColor" stroke-width="8"/>
                    <path d="M35 104c4-25 21-38 45-38s41 13 45 38" fill="none" stroke="currentColor" stroke-width="8" stroke-linecap="round"/>
                    <circle cx="30" cy="57" r="14" fill="none" stroke="currentColor" stroke-width="6"/>
                    <circle cx="130" cy="57" r="14" fill="none" stroke="currentColor" stroke-width="6"/>
                </svg>
                <h2 class="title">Users &amp; access</h2>
                <p class="content">Manage registered users, roles, account status and admin access from one place.</p>
                <a href="/en/admin/users" class="btn-syndicate dark big">Manage users</a>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="share-card blue">
                <svg class="icon" viewBox="0 0 160 120" role="img" aria-label="Platform controls">
                    <path d="M32 28h96M32 60h96M32 92h96" fill="none" stroke="currentColor" stroke-width="8" stroke-linecap="round"/>
                    <circle cx="62" cy="28" r="12" fill="currentColor"/>
                    <circle cx="104" cy="60" r="12" fill="currentColor"/>
                    <circle cx="76" cy="92" r="12" fill="currentColor"/>
                </svg>
                <h2 class="title">Platform controls</h2>
                <p class="content">Configure brands, categories, offers and global settings that control the platform.</p>
                <a href="/en/admin/content/settings" class="btn-syndicate dark big">Open controls</a>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-progress mb-3 p-4">
    <h3 class="dashboard-progress-title">Admin actions</h3>
    <div class="progress mt-3" style="height: 10px;">
        <div class="progress-bar bg-green" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <ul class="dashboard-profile-steps mt-3">
        <li class="row align-items-center">
            <div class="col-1 text-right">🪪</div>
            <div class="col">Review identity requests<div class="text-muted text-sm">Process pending KYC submissions</div></div>
            <div class="col-12 col-md-4 offset-1 offset-md-0 text-md-right"><a class="btn btn-light btn-sm" href="/en/admin/kyc">Open</a></div>
        </li>
        <li class="row align-items-center">
            <div class="col-1 text-right">👥</div>
            <div class="col">Review subscription activity<div class="text-muted text-sm">Open shared and joined subscription pages</div></div>
            <div class="col-12 col-md-4 offset-1 offset-md-0 text-md-right"><a class="btn btn-light btn-sm" href="/en/admin/shared-subscriptions">Open</a></div>
        </li>
        <li class="row align-items-center">
            <div class="col-1 text-right">⚙️</div>
            <div class="col">Manage admin settings<div class="text-muted text-sm">Update the signed-in administrator account</div></div>
            <div class="col-12 col-md-4 offset-1 offset-md-0 text-md-right"><a class="btn btn-light btn-sm" href="/en/admin/settings">Open</a></div>
        </li>
        <li class="row align-items-center">
            <div class="col-1 text-right">👤</div>
            <div class="col">Manage users<div class="text-muted text-sm">View, edit, promote or remove user accounts</div></div>
            <div class="col-12 col-md-4 offset-1 offset-md-0 text-md-right"><a class="btn btn-light btn-sm" href="/en/admin/users">Open</a></div>
        </li>
    </ul>
</div>
HTML;

        $platformSection = substr($html, $revenuesStart, $paymentsStart - $revenuesStart);
        $discoverSection = $this->discoverSection();
        $html = substr($html, 0, $shareStart).$platformSection.$discoverSection.$adminCards.substr($html, $paymentsStart);
        $pending = KycSubmission::where('status', KycSubmission::STATUS_UNDER_REVIEW)->count();
        $approved = KycSubmission::where('status', KycSubmission::STATUS_APPROVED)->count();
        $users = User::count();
        $values = [$pending, $approved, $users];
        $valueIndex = 0;
        $html = preg_replace_callback(
            '/<span class="stat-value(?: text-green| text-blue)?">€0<\/span>/',
            static function (array $match) use (&$valueIndex, $values): string {
                $value = $values[$valueIndex] ?? 0;
                $valueIndex++;

                return '<span class="stat-value">'.e((string) $value).'</span>';
            },
            $html,
            3,
        ) ?? $html;
        $html = str_replace(
            [
                'Income & expenses',
                'Monthly Savings',
                'Refund coming this month',
                'My monthly expenses',
                'See my wallet →',
                'See my expenses →',
                'Payments',
                "You've spent nothing",
                'Savings in sight',
            ],
            [
                'Platform overview',
                'Pending KYC reviews',
                'Approved identities',
                'Registered users',
                'Review KYC →',
                'Open subscriptions →',
                'Admin activity',
                'No payment tasks pending',
                'Platform is ready',
            ],
            $html,
        );

        return $html;
    }

    protected function discoverSection(): string
    {
        $source = file_get_contents(resource_path('captured/en/my-shared-subscriptions.html'));

        if ($source === false) {
            return '';
        }

        $start = strpos($source, '<div class="subscriptions-suggestions">');
        if ($start === false) {
            return '';
        }

        preg_match_all('/<\/?div\b[^>]*>/i', $source, $tags, PREG_OFFSET_CAPTURE, $start);
        $depth = 0;
        foreach ($tags[0] as [$tag, $offset]) {
            $depth += str_starts_with(strtolower($tag), '</div') ? -1 : 1;
            if ($depth === 0) {
                $section = substr($source, $start, $offset + strlen($tag) - $start);

                return str_replace(
                    '<div class="subscriptions-suggestions">',
                    '<div class="subscriptions-suggestions mt-4">',
                    $section,
                );
            }
        }

        return '';
    }
}
